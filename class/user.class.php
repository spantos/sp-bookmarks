<?php

/**
 * Class for managing users account
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
 * User class
 *
 * @package    User
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantoivc
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class User
{

    /**
     * Instance of database
     * @var $db PDO
     * @access private
     */
    private $db = null;

    /**
     * User email address
     * @var string
     * @access private
     */
    private $email;

    /**
     * User password
     * @var string
     * @access private
     */
    private $password;

    /**
     * After the method execution in this variable is stored information that is displayed to the user
     * @var string
     * @access private
     */
    private $user_status_message;

    /**
     * String attached to the user password
     * @var string
     * @access private
     */
    private $user_salt;

    /**
     * When the user verify the email address it has a value of 1
     * @var integer
     * @access private
     */
    private $verified = 0;

    /**
     * User data from database
     * @var array
     * @access private
     */
    private $user_data;

    /**
     * Minimum password length
     * @var integer
     * @access private
     */
    private $password_length = 8;

    /**
     * If user exist it has value true, otherwise false
     * If an error occurred in the database it contains string 'error'
     * @var mixed
     * @access private
     */
    private $user_exists = false;

    /**
     * Password salt length
     */
    const SALT_LENGTH = 64;

    /**
     * Length of string for verifying user email address
     */
    const VERIFY_STRING_LENGTH = 16;

    /**
     * Length of the code that will be sent via email to the user to change the forgotten password
     */
    const FORGOT_PASSWORD_CODE_LENGTH = 10;

    /**
     * Class constructor
     * Creating instance of database
     *
     * @access public
     */
    public function __construct()
    {
        $this->db = DataBase::dbConnect();
    }

    /**
     * Register a new user
     *
     * @param string $email Email address of the new user
     * @param string $password Parameter description (if any) ...
     * @return boolean           Return true if the new user successfully created
     * @access public
     */
    public function register($email, $password)
    {
        if (!$this->validateEmail($email)) {
            return false;
        } else {
            $this->email = strtolower($email);
        }
        if (!$this->validatePassword($password)) {
            return false;
        }
        $this->user_exists = $this->checkIfUserExists($this->email);
        if ($this->user_exists === 'error') {
            $this->user_status_message = 'An error has occurred. Try later.';
            return false;
        }
        if ($this->user_exists) {
            $this->user_status_message = 'There are a registered user with that data.';
            return false;
        }
        $this->password = $this->hashPassword($password);
        if (SEND_EMAIL_FOR_VERIFICATION === true) {
            $verify_token = $this->generateRandomString(self::VERIFY_STRING_LENGTH);
            $email_verification = new EmailVerification();
            $send_email = $email_verification->sendEmailMessage($this->email, $verify_token);
            if (!$send_email) {
                $this->user_status_message = 'Error sending email messages for verification. Please try again later.';
                return false;
            }
        } else {
            $this->verified = 1;
            $verify_token = null;
        }

        try {
            $date = date(DATETIME_FORMAT);
            $db_request = $this->db->prepare("INSERT INTO
                                            users (email,salt,password,verified,verify_token,registered)
                                            VALUES (:email,:salt,:password,:verified,:verify_token,:registered)");
            $db_request->bindParam(':email', $this->email);
            $db_request->bindParam(':salt', $this->user_salt);
            $db_request->bindParam(':password', $this->password);
            $db_request->bindParam(':verified', $this->verified);
            $db_request->bindParam(':verify_token', $verify_token);
            $db_request->bindParam(':registered', $date);
            $db_request->execute();
            if (SEND_EMAIL_FOR_VERIFICATION === true) {
                $this->user_status_message = 'Confirm your registration following the instructions in the email message.';
            } else {
                $this->user_status_message = 'Thank you for registering. You can sign in.';
            }
            return true;
        } catch (PDOException $e) {
            $this->user_status_message = 'An error has occurred. Try later.';
            DBErrorLog::loggingErrors($e);
            return false;
        }
    }

    /**
     * Login user
     *
     * @param string $eml User email address
     * @param string $password User password
     * @return boolean          Return true if the user has successfully logged
     * @access public
     */
    public function login($eml, $password)
    {
        if (!$this->validateEmail($eml)) {
            return false;
        }
        $this->email = $eml;
        $this->user_data = $this->getUserData($this->email);
        if ($this->user_data == 'error') {
            $this->user_status_message = 'An error has occurred. Try later.';
            return false;
        }
        if (!$this->user_data || ($this->user_data['verified'] !== 1)) {
            $this->user_status_message = 'You are not a registered user. Please register.';
            return false;
        }
        $this->user_salt = $this->user_data['salt'];
        $this->password = $this->hashPassword($password, $this->user_salt);
        if ($this->password !== $this->user_data['password']) {
            $this->user_status_message = 'You are not a registered user. Please register.';
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if the registered user already exists
     *
     * @param string $email Parameter description (if any) ...
     * @return mixed        Return true if an user exists, otherwise false.
     *                      If in the database an error occurred return string 'error'.
     * @access public
     */
    public function checkIfUserExists($email)
    {
        $data = $this->getUserData($email);
        if ($data === 'error') {
            return 'error';
        }
        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gives ID logged-in user.
     *
     * @return int  Return logged user ID
     * @access public
     */
    public function getLoggedUserId()
    {
        if (isset($this->user_data['id_user'])) {
            return intval($this->user_data['id_user']);
        } else {
            return false;
        }
    }

    /**
     * Gives a message about the status of the execution methods. It is displayed to the user.
     *
     * @return string Return message
     * @access public
     */
    public function getUserStatusMessage()
    {
        return $this->user_status_message;
    }

    /**
     * Changing forgotten password.
     *
     * @param string $email User email
     * @param string $forgot_password_code Code that will be sent via email to the user to change the forgotten password.
     * @param string $new_password New password
     * @param string $confirm_new_password String that must be same as password.
     * @return boolean
     * @access public
     */
    public function changeForgottenPassword($email, $forgot_password_code, $new_password, $confirm_new_password)
    {
        $email = strtolower($email);
        if (!$this->validateEmail($email)) {
            return false;
        }
        $user_exists = $this->checkIfUserExists($email);
        if ($user_exists === 'error') {
            $this->user_status_message = 'An error has occurred. Try later.';
            return false;
        }
        if (!$user_exists) {
            $this->user_status_message = 'We could not find the email address you entered in our system.';
            return false;
        }
        if (!$this->validateForgotPasswordCode($forgot_password_code)) {
            return false;
        }
        if ($new_password !== $confirm_new_password) {
            $this->user_status_message = 'Password confirmation is not the same as the password.';
            return false;
        }
        if (!$this->validatePassword($new_password)) {
            return false;
        }
        $password = $this->hashPassword($new_password);
        if ($this->changePassword($email, $password)) {
            return true;
        } else {
            $this->user_status_message = 'The password is not changed. An error has occurred. Try later.';
            return false;
        }
    }

    /**
     * Sending code to the user email to verify its identity to be able changing the password.
     *
     * @param string $user_email
     * @return boolean Return true if an email is sent successfully, otherwise false.
     * @access public
     */
    public function sendForgotPasswordCode($user_email)
    {
        $forgot_password_code = $this->generateForgotPasswordCode($user_email);
        $subject = 'Request for changing password on SP Bookmarks.';
        $body = "<p>You have sent a request to change the password. The code of this email copy in the appropriate field on the page to change your password.</p>
                        <p>Code for changing password: {$forgot_password_code}</p>";
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
     * Validate user's email address.
     *
     * @param string $email Parameter description (if any) ...
     * @return boolean Return description (if any) ...
     * @access private
     */
    private function validateEmail($email)
    {
        /*
        if (!preg_match_all('/^[a-z0-9\.\_\-@]*$/',$email)){
        $this->user_status_message='For email use letters,underscore,hyphen,numbers and periods.';
        return false;
        }
        */
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            $this->user_status_message = 'Invalid email address.';
            return false;
        }
    }

    /**
     * Validate user password
     *
     * Password must have at least eight characters and must have letters and numbers.
     *
     * @param string $password
     * @return boolean        Returns true if the password is valid, otherwise false.
     * @access private
     */
    private function validatePassword($password)
    {
        if (strlen($password) < $this->password_length) {
            $this->user_status_message = 'Password must have at least eight characters.';
            return false;
        }
        if (preg_match("/(?=[a-zA-Z]*[0-9])(?=[0-9]*[a-zA-Z])([a-zA-Z0-9]+)/", $password)) {
            return true;
        } else {
            $this->user_status_message = 'Password must heave letters and numbers.';
            return false;
        }
    }

    /**
     * Generate a random string for the password hashing and secure code for changing forgotten password.
     *
     * @param int $length Length of the random string.
     * @return string     Returns generated random string.
     * @access private
     */
    private function generateRandomString($length)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * Password hashing
     *
     * @param string $password Password for hashing
     * @param string $salt
     * @return string  Returns hashed password
     * @access private
     */
    private function hashPassword($password, $salt = null)
    {
        if ($salt === null) {
            $this->user_salt = $this->generateRandomString(self::SALT_LENGTH);
        }
        $password = $password . $this->user_salt;
        $password = hash('sha512', $password);
        return $password;
    }

    /**
     * Gives user data from database.
     *
     * Long description (if any) ...
     *
     * @param string $email User email
     * @return mixed        Return array if user exist, otherwise false.
     *                      If an error occurred in the database return string 'error'.
     * @access private
     */
    private function getUserData($email)
    {
        try {
            $db_request = $this->db->prepare("SELECT * FROM users WHERE email=:email LIMIT 1");
            $db_request->bindParam('email', $email);
            $db_request->execute();
            if ($db_request->rowCount()) {
                $res = $db_request->fetch(PDO::FETCH_ASSOC);
                return $res;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
            return 'error';
        }
    }

    /**
     * Validating code to change forgotten passwords
     *
     * @param string $code
     * @return boolean Return true if code is valid
     * @access private
     */
    private function validateForgotPasswordCode($code)
    {
        try {
            $db_request = $this->db->prepare("SELECT forgot_password_code 
                                              FROM users WHERE forgot_password_code=:forgot_password_code LIMIT 1");
            $db_request->bindParam(':forgot_password_code', $code);
            $db_request->execute();
            if ($db_request->rowCount()) {
                return true;
            } else {
                $this->user_status_message = 'Enter the appropriate code to change the password.';
                return false;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
    }

    /**
     * Generate secret code for password reset
     *
     * @param string $email User's email address
     * @return boolean Return true if the code successfully generated, otherwise return false
     * @access private
     */
    private function generateForgotPasswordCode($email)
    {
        try {
            $forgot_password_code = $this->generateRandomString(self::FORGOT_PASSWORD_CODE_LENGTH);
            $db_query = $this->db->prepare("UPDATE users SET forgot_password_code=:forgot_password_code 
                                            WHERE email=:email AND verified=1 LIMIT 1");
            $db_query->bindParam(':forgot_password_code', $forgot_password_code);
            $db_query->bindParam(':email', $email);
            $db_query->execute();
            if ($db_query->rowCount()) {
                return $forgot_password_code;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
            return false;
        }
    }

    /**
     *   Changing the password.
     *
     * @param string $email User's email address
     * @param string $new_password New password
     * @return boolean Return true if the password is changed
     * @access private
     */
    private function changePassword($email, $new_password)
    {
        try {
            $db_request = $this->db->prepare('UPDATE users SET password=:new_password,forgot_password_code=NULL,
                                              salt=:salt WHERE email=:email LIMIT 1');
            $db_request->bindParam(':new_password', $new_password);
            $db_request->bindParam(':salt', $this->user_salt);
            $db_request->bindParam(':email', $email);
            $db_request->execute();
            if ($db_request->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
    }
}
