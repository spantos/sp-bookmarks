<?php

require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
if (!isset($_SESSION['logged_user_id']) || !$session->verifySession($_SESSION['token'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
if (!isset($_GET['browser'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
$browser = filter_input(INPUT_GET, 'browser', FILTER_SANITIZE_STRING);
$export = new Export($_SESSION['logged_user_id'], $browser);
