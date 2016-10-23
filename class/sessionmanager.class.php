<?php

/**
 * Management with php sessions
 *
 * PHP version 5
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantovic
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 */


/**
 * Management with php sessions
 *
 * @package   SessionManager
 * @author    Slobodan Pantovic spbookmarks@gmail.com
 * @copyright 2016 Slobodan Pantovic
 * @license   http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version   0.1.0
 */
class SessionManager
{

    /**
     * Session name
     *
     * If you change this value, you must change the properties $session_name
     * in the file securimage.php on line 441
     *
     * @var string
     * @access private
     */
    private $session_name = 'sp-bookmarks';

    /**
     * Lifetime of the session cookie in seconds
     * @var int
     * @access private
     */
    private $lifetime;

    /**
     * Path on the domain where the cookie will work
     * @var string
     * @access private
     */
    private $path;

    /**
     * Cookie domain
     * @var string
     * @access private
     */
    private $domain = null;

    /**
     * If true cookie will only be sent over secure connections
     * @var boolean
     * @access private
     */
    private $secure = false;

    /**
     * Session id before regenerate session id
     * @var string
     * @access private
     */
    private $old_session_id;

    /**
     * Session id after regenerate session id
     * @var string
     * @access private
     */
    private $current_session_id;

    /**
     * Database connection
     * @var PDO
     * @access private
     */
    private $db = null;

    /**
     * Duration of the current session expressed by minute
     * @var integer
     * @access private
     */
    private $session_timeout = 60;

    /**
     * Session salt length
     */
    const SALT_LENGTH = 32;

    /**
     * Class constructor
     *
     * @param integer $lifetime Lifetime of the session cookie in seconds
     * @param string $path Path on the domain where the cookie will work
     * @param string $domain Cookie domain
     * @param boolean $secure If true cookie will only be sent over secure connections
     * @access public
     */
    public function __construct($lifetime = 0, $path = '/', $domain = null, $secure = null)
    {
        $this->db = DataBase::dbConnect();
        $this->lifetime = $lifetime;
        $this->path = $path;
        $this->domain = isset($domain) ? $domain : $_SERVER['SERVER_NAME'];
        $this->secure = isset($secure) ? $secure : isset($_SERVER['HTTPS']);
        $this->deleteOldSession();
        $this->sessionSetup();
    }

    /**
     * Session start
     *
     * @return void
     * @access public
     */
    public function sessionStart()
    {
        if (session_status() === 1) {
            session_start();
            $session_id = session_id();
            $session_date = date(DATETIME_FORMAT);
            if (!$this->checkIfSessionExists($session_id)) {
                $session_salt = $this->generateSessionSalt();
                $db_query = $this->db->prepare('INSERT INTO session (session_id,session_salt,session_date)
                                                VALUES(:session_id,:session_salt,:session_date)');
                $db_query->bindParam(':session_id', $session_id);
                $db_query->bindParam(':session_salt', $session_salt);
                $db_query->bindParam(':session_date', $session_date);
                $db_query->execute();
            }
            $this->regenerateId();
        }
        return;
    }

    /**
     * Session destroy
     *
     * @return void
     * @access public
     */
    public function sessionDestroy()
    {
        $this->deleteSession();
        $_SESSION = array();
        session_destroy();
    }

    /**
     * Verify session
     *
     * @param string $token
     * @return boolean Returns true if the session is verified
     * @access public
     */
    public function verifySession($token)
    {
        if (!isset($_COOKIE[session_name()]) || $token !== $this->getSessionToken()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get session token
     *
     * @return mixed Return false if session not exists
     *               Return session token if session exists
     * @access public
     */
    public function getSessionToken()
    {
        if (session_status() === 1) {
            return false;
        } else {
            if ($salt = $this->getSessionSalt()) {
                $token = hash('sha512', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $salt['session_salt']);
                return $token;
            } else {
                return false;
            }
        }
    }

    /**
     * Session setup
     *
     * @return void
     * @access private
     */
    private function sessionSetup()
    {
        ini_set('session.use_only_cookies', true);
        if (version_compare(phpversion(), '5.5.2', '>=')) {
            ini_set('session.use_strict_mode', true);
        }
        session_name($this->session_name);
        session_set_cookie_params($this->lifetime, $this->path, $this->domain, $this->secure, true);
    }

    /**
     * Regenerate session ID
     *
     * @return void
     * @access private
     */
    private function regenerateId()
    {
        $this->old_session_id = session_id();
        session_regenerate_id(true);
        $this->current_session_id = session_id();
        $this->updateSessionId();
    }

    /**
     * Delete session
     *
     * @return void
     * @access private
     */
    private function deleteSession()
    {
        $db_query = $this->db->prepare('DELETE FROM session WHERE session_id=:current_session_id LIMIT 1');
        $db_query->bindParam(':current_session_id', $this->current_session_id);
        $db_query->execute();
    }

    /**
     * Get session salt
     *
     * @return mixed Return session salt. If in the database has no records return false.
     * @access private
     */
    private function getSessionSalt()
    {
        try {
            $db_request = $this->db->prepare('SELECT session_salt FROM session WHERE session_id=:session_id LIMIT 1');
            $db_request->bindParam(':session_id', $this->current_session_id);
            $db_request->execute();
            if ($db_request->rowCount()) {
                $res = $db_request->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
    }

    /**
     * Generate session salt
     *
     * @return string
     * @access private
     */
    private function generateSessionSalt()
    {
        return bin2hex(openssl_random_pseudo_bytes(self::SALT_LENGTH));
    }

    /**
     * Update session data in database after regenerate ID
     *
     * @return void
     * @access private
     */
    private function updateSessionId()
    {
        $session_date = date(DATETIME_FORMAT);
        $db_query = $this->db->prepare('UPDATE session SET
                                        session_id=:session_id,
                                        session_date=:session_date
                                        WHERE session_id=:old_session_id LIMIT 1');
        $db_query->bindParam(':session_id', $this->current_session_id);
        $db_query->bindParam(':session_date', $session_date);
        $db_query->bindParam(':old_session_id', $this->old_session_id);
        $db_query->execute();
    }

    /**
     * Check if session exists
     *
     * Long description (if any) ...
     *
     * @param string $session_id
     * @return boolean Return true if exists otherwise false
     * @access private
     */
    private function checkIfSessionExists($session_id)
    {
        $db_query = $this->db->prepare('SELECT session_id FROM session WHERE session_id=:session_id LIMIT 1');
        $db_query->bindParam(':session_id', $session_id);
        $db_query->execute();
        if ($db_query->rowCount()) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Delete old session from database
     *
     * Delete session data from database when expired appropriate time of session duration
     * defined in attribute $session_timeout
     *
     * @return void
     * @access private
     */
    private function deleteOldSession()
    {
        try {
            $db_query = $this->db->prepare('DELETE FROM session WHERE session_date < NOW() - INTERVAL ? MINUTE');
            $db_query->execute(array($this->session_timeout));
        } catch (PDOexception $e) {
            DBErrorLog::loggingErrors($e);
        }
    }
}
