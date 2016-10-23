<?php
require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
$session->sessionDestroy();
header('location:' . SITE_URL);
exit;
