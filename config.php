<?php

/**
 * The base configuration
 *
 * PHP version 5
 *
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantovic
 */

// Site URL protocol
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';

$request_uri = $_SERVER['REQUEST_URI'];

// Set the appropriate time zone.
// Coresponding parameters You can find on link http://php.net/manual/en/timezones.php
date_default_timezone_set('');

/**
 * Absolute path to the application.
 */
define('ABS_PATH', __DIR__);

/**
 * Site URL without slash at the end
 */
define('SITE_URL', $protocol . $_SERVER['SERVER_NAME'] . substr($request_uri, 0, strrpos($request_uri, '/' )));

/**
 *  Change this to true to enable the display of notices.
 */
define('DISPLAY_ERRORS', false);

/**
 * Default Date/Time format
 */
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

/**
 * Change this to false to disable displaying registration form.
 */
define('REGISTER_FORM', true);

/**
 * Change this to false to disable send email for verification.
 */
define('SEND_EMAIL_FOR_VERIFICATION', true);

/**
 * To send e-mail you can use sendmail or SMTP. Sendmail is default.
 * If you want to use SMTP change the value to true.
 * In the corresponding classes write necessary data of the email server.
 */
define('USE_SMTP', false);

/**
 * The name of the database.
 */
define('DB_NAME', '');

/**
 * MySQL database username.
 */
define('DB_USER', '');

/**
 * MySQL database password.
 */
define('DB_PASSWORD', '');

/**
 * MySQL hostname
 */
define('DB_HOST', 'localhost');

/**
 * Database errors log file location.
 */
define('DB_ERROR_LOG_FILE', ABS_PATH . '/logs/dberror.log');

/**
 * Email errors log file location.
 */
define('EMAIL_ERROR_LOG_FILE', ABS_PATH . '/logs/emailerror.log');

/**
 * Autoload class
 *
 * @param string $class Class name
 * @return void
 */
function autoloadClass($class)
{
    $file = ABS_PATH."/class/" . strtolower($class) . ".class.php";
    if (file_exists($file)) {
        require $file;
    }
}

/**
 * Autoload Phpmailer class.
 *
 * @param object $class Class name
 * @return void
 */
function phpMailer($class)
{
    $file = ABS_PATH."/library/phpmailer/" . "class."  . strtolower($class) . ".php";
    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('autoloadClass');
spl_autoload_register('phpMailer');

error_reporting(E_ALL | E_STRICT);
if (DISPLAY_ERRORS === false) {
    ini_set('display_errors', 'off');
    ini_set('log_errors', 'on');
    ini_set('error_log', ABS_PATH . '/logs/phperror.log');
}
