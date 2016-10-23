<?php

/**
 * Send email message
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
 * Send email message
 *
 * @package    SmtpEmail
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantoivc
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class SmtpEmail
{

    /**
     * SMTP host
     * @var string
     * @access private
     */
    private $host = '';

    /**
     * set the SMTP port for the server
     * @var integer
     * @access private
     */
    private $port;

    /**
     * SMTP HOST user name
     * @var string
     * @access private
     */
    private $host_user_name = '';

    /**
     * SMTP HOST user password
     * @var string
     * @access private
     */
    private $host_user_password = '';

    /**
     * Valid email address of the sending email message.
     * @var string
     * @access private
     */
    private $set_from = '';

    /**
     * Username sender's email message.
     * @var string
     * @access private
     */
    private $set_from_user_name = '';

    /**
     * The user's email address to which is sent email message.
     * @var string
     * @access private
     */
    private $user_email;

    /**
     * Subject of email message.
     * @var string
     * @access private
     */
    private $email_subject;

    /**
     * The content of email message.
     * @var string
     * @access private
     */
    private $email_body;

    /**
     * Class constructor
     *
     * @param string $user_email The user's email address to which is sent email messages
     * @param string $subject Subject of email message
     * @param string $body The content of email messages
     * @access public
     */
    public function __construct($user_email, $subject, $body)
    {
        $this->user_email = filter_var($user_email, FILTER_SANITIZE_EMAIL);
        $this->email_subject = filter_var($subject, FILTER_SANITIZE_STRING);
        $this->email_body = $body;
    }

    /**
     * Sending an email message.
     *
     * @return boolean Returns true if the email message successfully sent otherwise returns false
     * @access public
     */
    public function sendMessage()
    {
        $email = new PHPMailer(true);
        try {
            $email->isSMTP();
            $email->CharSet = 'UTF-8';
            $email->SMTPDebug = 0;
            $email->Debugoutput = 'html';

            $email->Host = $this->host;
            $email->Port = $this->port;
            $email->SMTPAuth = true;
            $email->SMTPSecure = 'tls';
            $email->isHTML(true);

            $email->Username = $this->host_user_name;
            $email->Password = $this->host_user_password;

            $email->setFrom($this->set_from, $this->set_from_user_name);
            $email->addAddress($this->user_email, '');

            $email->Subject = $this->email_subject;
            $email->Body = $this->email_body;
            $email->send();
            return true;
        } catch (phpmailerException $e) {
            error_log(date(DATETIME_FORMAT) . '[' . $e->getFile() . '] ' . '[line:' . $e->getLine() . '] '
                . '[message:' . $e->getMessage() . ']' . PHP_EOL, 3, EMAIL_ERROR_LOG_FILE);
            return false;
        }
    }
}
