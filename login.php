<?php
require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
$token = $session->getSessionToken();
require_once ABS_PATH . '/library/securimage/securimage.php';
$securimage = new Securimage();
if (!isset($_POST['token']) || (!$session->verifySession($_SESSION['token'])) || ($_POST['token'] !== $token)) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
if (!($email = filter_input(INPUT_POST, 'login_email', FILTER_SANITIZE_EMAIL))) {
    $_SESSION['login_message'] = 'Please enter your email.';
    if (isset($_POST['captcha_code'])) {
        header('location:login-captcha.php');
        exit;
    } else {
        header('location:index.php');
        exit;
    }
}
if (!($password = filter_input(INPUT_POST, 'login_password', FILTER_SANITIZE_STRING))) {
    $_SESSION['login_email'] = $email;
    $_SESSION['login_message'] = 'Please enter your password.';
    if (isset($_POST['captcha_code'])) {
        header('location:login-captcha.php');
        exit;
    } else {
        header('location:index.php');
        exit;
    }
}
if (isset($_POST['captcha_code'])) {
    if ($securimage->check($_POST['captcha_code']) == false) {
        $_SESSION['login_email'] = $email;
        $_SESSION['login_message'] = 'The security code entered was incorrect.';
        $_SESSION['captcha-token'] = $_SESSION['token'];
        header('location:login-captcha.php');
        exit;
    }
}
$user = new User();
if (!$user->login($email, $password)) {
    $_SESSION['login_message'] = $user->getUserStatusMessage();
    $_SESSION['login_email'] = $email;
    $login_attempt = new LoginAttempt($email);
    if ($login_attempt->getLoginAttemptStatus() === 'captcha') {
        $_SESSION['captcha-token'] = $_SESSION['token'];
        header('location:login-captcha.php');
        exit();
    } else {
        header('location:index.php');
        exit;
    }
} else {
    $_SESSION['logged_user_id'] = $user->getLoggedUserId();
    if ($_SESSION['logged_user_id']) {
        header('location:bookmarks.php');
        exit();
    } else {
        die();
    }
}
