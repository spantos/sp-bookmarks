<?php

require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
if (!isset($_SESSION['logged_user_id']) || !isset($_SESSION['token'])) {
    $session->sessionDestroy();
    header('location:' . SITE_URL);
    exit;
}
if (isset($_SESSION['logged_user_id']) && !$session->verifySession($_SESSION['token'])) {
    echo 'Session has expired. Please log in again.';
    $session->sessionDestroy();
    exit;
}
$id_user = $_SESSION['logged_user_id'];
if (isset($_POST['gr_name'])) {
    $group_name = trim(filter_input(INPUT_POST, 'gr_name', FILTER_SANITIZE_STRING));
    if ($group_name == false) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo "Enter the name of the group.";
        exit;
    } else {
        $group = new Group();
        $group->createGroup($id_user, $group_name);
        header('Content-Type:text/plain;charset=UTF-8');
        echo $group->getStatus();
        exit;
    }
}

if (isset($_POST['bookmark_name']) || isset($_POST['bookmark_url']) || isset($_POST['bookmark_group'])) {
    if (empty($_POST['bookmark_name'])) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo 'Enter the bookmark name';
        exit;
    }
    if (empty($_POST['bookmark_url'])) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo 'Enter the bookmark URL';
        exit;
    }
    if (empty($_POST['bookmark_group'])) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo 'Select group.';
        exit;
    }
    $bookmark_name = filter_input(INPUT_POST, 'bookmark_name', FILTER_SANITIZE_STRING);
    $bookmark_url = filter_input(INPUT_POST, 'bookmark_url', FILTER_SANITIZE_URL);
    if (preg_match('/\/$/', $bookmark_url)) {
        $bookmark_url = rtrim($bookmark_url, '/');
    }
    if (!filter_var($bookmark_url, FILTER_VALIDATE_URL)) {
        $bookmark_url = 'http://' . $bookmark_url;
        if (!filter_var($bookmark_url, FILTER_VALIDATE_URL)) {
            header('Content-Type:text/plain;charset=UTF-8');
            echo "Not valid URL.";
            exit;
        }
    }
    $bookmark_group = filter_input(INPUT_POST, 'bookmark_group', FILTER_SANITIZE_STRING);
    $bookmark_description = filter_input(INPUT_POST, 'bookmark_description', FILTER_SANITIZE_STRING);
    $bookmark = new Bookmark();
    $bookmark->saveBookmark(
        $bookmark_name,
        $bookmark_url,
        $bookmark_group,
        $_SESSION['logged_user_id'],
        $bookmark_description
    );
    header('Content-Type:text/plain;charset=UTF-8');
    echo $bookmark->getStatus();
    exit;
}
