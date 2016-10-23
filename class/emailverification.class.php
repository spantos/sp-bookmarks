<?php

/**
 * Email verification
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
 * @version    0.1.0
 */


/**
 * Email verification
 *
 * @package    EmailVerification
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantoivc
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class EmailVerification
{

    /**
     * Contains a message that is displayed to the user during registration.
     * @var string
     * @access private
     */
    private $register_message;

    /**
     * Instance of database
     * @var PDO
     * @access private
     */
    private $db;
    
    /**
     * Create database instance.
     *
     * @access public
     */
    public function __construct()
    {
        $this->db = DataBase::dbConnect();
    }

    /**
     * Verifying email address.
     *
     * @param string $token String that is sent to the user's email after registration.
     * @return boolean      Returns true if the email has been verified otherwise false.
     * @access public
     */
    public function verifyEmailAddress($token)
    {
        try {
            $db_request = $this->db->prepare('SELECT verify_token FROM users
                                            WHERE verify_token=:token AND verified=0 LIMIT 1');
            $db_request->bindParam(':token', $token);
            $db_request->execute();
            $res = $db_request->fetch(PDO::FETCH_ASSOC);
            if ($res['verify_token'] === $token) {
                if ($this->verifyEmail($token)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->logError($e);
        }
    }

    /**
     * Sending confirmation link on the user provided email address.
     *
     * @param string $user_email
     * @param string $verify_token
     * @return boolean Returns true if the email is sent successfully, otherwise false.
     * @access public
     */
    public function sendEmailMessage($user_email, $verify_token)
    {
        $verify_url = SITE_URL . '/verify-email.php?token=';
        $subject = 'Please verify your email address.';
        $body = "<p>Thanks for creating an account with SP Bookmarks. Click below to confirm your email address:<br><br>
                 <a href='{$verify_url}{$verify_token}'>{$verify_url}{$verify_token}</a></p>
                 <p>If you have problems, please paste the above URL into your web browser.</p>
                 <p>If you did not register for an account, please just ignore this email and we won't bother you again.</p>";
        
        if (USE_SMTP) {
            $email = new SmtpEmail($user_email, $subject, $body);
        } else {
            $email = new Sendmail($user_email, $subject, $body);
        }
        
        if ($email->sendMessage()) {
            return true;
        } else {
            return false;
        }
        
    }
    
        /**
     * Validates email address.
     *
     * @param string $token String that is sent to the user's email after registration.
     * @return boolean Returns true if the email address is verified, otherwise false.
     * @access private
     */
    private function verifyEmail($token)
    {
        try {
            $db_request = $this->db->prepare('UPDATE users SET verify_token=null,verified=1
                                            WHERE verify_token=:token');
            $db_request->bindParam(':token', $token);
            $db_request->execute();
            if ($db_request->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->logError($e);
        }
    }
}
