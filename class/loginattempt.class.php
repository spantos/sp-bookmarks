<?php

/**
 * Records failed login
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
 * Records failed login
 *
 * @package    LoginAttempt
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantoivc
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class LoginAttempt
{

    /**
     * The maximum number of failed login before display captcha
     */
    const MAX_ATTEMPT_BEFORE_CAPTCHA = 3;

    /**
     * Instance of database
     * @var PDO
     * @access private
     */
    private $db;

    /**
     * If the number of failed login attempt greater than the maximum contains string 'captcha' otherwise null
     * @var string
     * @access private
     */
    private $login_attempt_status;

    /**
     * Number of days after which automatically deleted failed login
     */
    const LOGIN_ATTEMPT_EXPIRATION = 2;

    /**
     * Constructor
     *
     * Create database instance, delete old bad attempts login,
     * add new bad attempts login and set value for the properties $login_attempt_status
     *
     * @param string $email User email address
     * @access public
     */
    public function __construct($email)
    {
        $this->db = DataBase::dbConnect();
        $this->deleteOldFailedLogin();
        $this->addFailedLogin($email);
        if ($this->getNumberOfBadAttempts($email) > self::MAX_ATTEMPT_BEFORE_CAPTCHA) {
            $this->login_attempt_status = 'captcha';
        } else {
            $this->login_attempt_status = null;
        }
    }

    /**
     * Return status of login attempt
     *
     * If the number of failed login attempt greater than the maximum return string 'captcha'.
     * In this case you should show captcha to login page.
     * Otherwise returns null.
     *
     * @return string
     * @access public
     */
    public function getLoginAttemptStatus()
    {
        return $this->login_attempt_status;
    }

    /**
     * Adds a failed login for a given e-mail address
     *
     * @param string $email User e-mail address
     * @return void
     * @access private
     */
    private function addFailedLogin($email)
    {
        try {
            $login_attempt_date = date(DATETIME_FORMAT);
            $db_request = $this->db->prepare('INSERT INTO login_attempts 
                                            (email,login_attempt_date) 
                                            VALUES (:email,:login_attempt_date)');
            $db_request->bindParam(':email', $email);
            $db_request->bindParam(':login_attempt_date', $login_attempt_date);
            $db_request->execute();
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
    }

    /**
     * Returns the number of failed login for a given e-mail address
     *
     * @param string $email User e-mail address
     * @return mixed
     * @access private
     */
    private function getNumberOfBadAttempts($email)
    {
        try {
            $db_query = $this->db->prepare('SELECT count(*) AS count FROM login_attempts WHERE email=:email');
            $db_query->bindParam(':email', $email);
            $db_query->execute();
            if ($db_query->rowCount()) {
                $res = $db_query->fetch(PDO::FETCH_ASSOC);
                return $res['count'];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
    }

    /**
     * Deleting old failed login attempts.
     *
     * @return void
     * @access private
     */
    private function deleteOldFailedLogin()
    {
        try {
            $db_query = $this->db->prepare('DELETE FROM login_attempts WHERE login_attempt_date < NOW() - INTERVAL ? DAY');
            $db_query->execute(array(self::LOGIN_ATTEMPT_EXPIRATION));
        } catch (PDOexception $e) {
            DBErrorLog::loggingErrors($e);
        }
    }
}
