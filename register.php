<?php
require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
if (!isset($_POST['token']) || !$session->verifySession($_POST['token'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
if (!($email = filter_input(INPUT_POST, 'reg_email', FILTER_SANITIZE_EMAIL))) {
        $_SESSION['register_message'] = "Please enter your email.";
        header('location:index.php');
        exit;
} elseif (!($password = filter_input(INPUT_POST, 'reg_password', FILTER_SANITIZE_STRING))) {
        $_SESSION['reg_email'] = $email;
        $_SESSION['register_message'] = "Please enter your password.";
        header('location:index.php');
        exit;
} elseif (!($password2 = filter_input(INPUT_POST, 'reg_password2', FILTER_SANITIZE_STRING))) {
        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_password'] = $password;
        $_SESSION['register_message'] = "Confirm password";
        header('location:index.php');
        exit;
} elseif ($password !== $password2) {
        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_password'] = $password;
        $_SESSION['reg_password2'] = $password2;
        $_SESSION['register_message'] = "Please enter the same password as above.";
        header('location:index.php');
        exit;
} else {
    $user=new User();
    if (!$user->register($email, $password)) {
        $_SESSION['register_message'] = $user->getUserStatusMessage();
        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_password'] = $password;
        $_SESSION['reg_password2'] = $password2;
        header('location:index.php');
        exit;
    } else {
        $_SESSION['register_message'] = $user->getUserStatusMessage();
        header('location:index.php');
        exit;
    }
}
