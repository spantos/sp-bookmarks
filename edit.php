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
if (isset($_POST['edit_id_group'])) {
    $id_user = $_SESSION['logged_user_id'];
    $id_group = filter_input(INPUT_POST, 'edit_id_group', FILTER_SANITIZE_STRING);
    $new_group_name = $_POST['new_group_name'];
    $group = new Group();
    $group->editGroupName($id_user, $new_group_name, $id_group);
    echo $group->getStatus();
    exit;
}
if (isset($_POST['del_id_group'])) {
    $id_group = filter_input(INPUT_POST, 'del_id_group', FILTER_SANITIZE_STRING);
    $id_user = $_SESSION['logged_user_id'];
    $group = new Group();
    $group->deleteGroup($id_user, $id_group);
    echo $group->getStatus();
    exit;
}
if (isset($_POST['get_bookmark'])) {
    $bookmark_id = filter_input(INPUT_POST, 'get_bookmark', FILTER_SANITIZE_STRING);
    $id_user = $_SESSION['logged_user_id'];
    $bookmark = new Bookmark();
    if ($bookmark_data = $bookmark->getBookmark($bookmark_id, $id_user)) {
        $json = json_encode($bookmark_data);
        header('Content-Type: application/json; charset=utf-8');
        header('Expires: 0');
        echo $json;
        exit;
    } else {
        echo $bookmark->getStatus();
        exit;
    }
}

if (isset($_POST['delete_bookmark_id'])) {
    $bookmark_id = filter_input(INPUT_POST, 'delete_bookmark_id', FILTER_SANITIZE_STRING);
    $id_user = $_SESSION['logged_user_id'];
    $bookmark = new Bookmark();
    $bookmark->deleteBookmark($bookmark_id, $id_user);
    echo $bookmark->getStatus();
    exit;
}
if (isset($_POST['edit_bookmark_id'])) {
    if ($_POST['bookmark_name'] == false) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo 'Enter the bookmark name';
        exit;
    }
    if ($_POST['bookmark_url'] == false) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo 'Enter the bookmark URL';
        exit;
    }
    if ($_POST['bookmark_id_group'] == false) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo 'Select group.';
        exit;
    }
    if (isset($_POST['bookmark_description'])) {
        $bookmark_description = filter_input(INPUT_POST, 'bookmark_description', FILTER_SANITIZE_STRING);
    } else {
        $bookmark_description = "";
    }
    $id_user = $_SESSION['logged_user_id'];
    $bookmark_id = filter_input(INPUT_POST, 'edit_bookmark_id', FILTER_SANITIZE_STRING);
    $bookmark_name = filter_input(INPUT_POST, 'bookmark_name', FILTER_SANITIZE_STRING);
    $id_group = filter_input(INPUT_POST, 'bookmark_id_group', FILTER_SANITIZE_STRING);
    $bookmark_url = filter_input(INPUT_POST, 'bookmark_url', FILTER_SANITIZE_URL);
    if (!preg_match('/^(https?:\/\/)/', $bookmark_url)) {
        $bookmark_url = 'http://' . $bookmark_url;
    }
    if (preg_match('/\/$/', $bookmark_url)) {
        $bookmark_url = rtrim($bookmark_url, '/');
    }
    if (!filter_var($bookmark_url, FILTER_VALIDATE_URL)) {
        header('Content-Type:text/plain;charset=UTF-8');
        echo "Not valid URL.";
        exit;
    }
    $bookmark = new Bookmark();
    $bookmark->editBookmark($bookmark_id, $id_user, $bookmark_name, $bookmark_url, $id_group, $bookmark_description);
    echo $bookmark->getStatus();
    exit;
}

header('location:index.php');
exit;
