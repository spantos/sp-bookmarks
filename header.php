<?php
require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
if (!isset($_SESSION['logged_user_id']) || !$session->verifySession($_SESSION['token'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>Bookmarks</title>
	<link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
	<link href="css/main.css" type="text/css" rel="stylesheet" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>

        <nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
        
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="bookmarks.php" class="navbar-brand">Bookmarks</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="menu">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="" data-toggle="modal" data-target="#modalGroup">Add group</a></li>
        <li><a href="" data-toggle="modal" data-target="#modalBookmark">Add bookmark</a></li>
        <li class="dropdown">
       <a href="#" class="dropdown-toggle" data-toggle='dropdown' role='button' aria-haspopup="true" aria-expanded="false">Export<span class="caret"></span></a>
       <ul class="dropdown-menu">
            <?php
            $registered_browser = Export::getRegisteredBrowser();
            if (is_array($registered_browser) && !empty($registered_browser)) {
                foreach ($registered_browser as $browser) {
                    echo "<li><a href='export.php?browser={$browser['url_get']}'>{$browser['menu_item']}</a></li>" . PHP_EOL;
                }
            } else {
                throw new Exception('Attribute Export::$register_browser is not array or is empty.');
            }
            ?>
          </ul>  
        </li>
        <li><a href="logout.php">Log out</a></li>
      <form class="navbar-form navbar-right" role="search" action="search.php" method="post">
          <input type="text" class="form-control" placeholder="Search" name="search" />
        <button type="submit" class="btn btn-default">Search</button>
      </form>
    </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="container">

<!-- Modal Group -->
<div class="modal fade" id="modalGroup" tabindex="-1" role="dialog" aria-labelledby="modalGroupLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalGroupLabel">Add Group</h4>
      </div>
      <div class="modal-body">
        <form name="bookmarks-group" id="bookmarks-group" method="post" action="">
        <div class="form-group">
        <label for="group-name">New Group Name</label>
        <input class="form-control" type="text" name="group-name" id="group-name" />
        <div id="group-message"></div>
      </div>
      <div class="modal-footer">
            <input type="button" class="btn btn-primary" value="Add Group" id="modal-group" />
            <input type="submit" class="btn btn-default" value="Close" />
      </div>
      </form>
    </div>
  </div>
</div> 
</div><!-- End of modal group-->

<!-- Modal Bookmark -->
<div class="modal fade" id="modalBookmark" tabindex="-1" role="dialog" aria-labelledby="modalBookmarkLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalBookmarkLabel">Add Bookmark</h4>
      </div>
      <div class="modal-body">
        <form name="bookmark" id="bookmark" method="post" action="">
        <div class="form-group">
        <label for="bookmark-name">Name:</label>
        <input class="form-control" type="text" name="bookmark-name" id="bookmark-name" />
        <label for="bookmark-url">Address (URL):</label>
        <input class="form-control" type="text" name="bookmark-url" id="bookmark-url" />
        <label for="bookmark-description">Description:</label>
        <textarea name="bookmark-description" id="bookmark-description" class="form-control"></textarea>
        <label for="bookmark-group">Groups</label>
        <select name="bookmark-group" class="form-control" id="bookmark-group">
        <?php
        $bookmarks_query = new BookmarksQuery();
        $groups = $bookmarks_query->getAllGroups($_SESSION['logged_user_id']);
        $all_bookmarks = $bookmarks_query->getAllBookmarks($_SESSION['logged_user_id']);
        echo '<option selected disabled value="">Select the group</option>' . PHP_EOL;
        foreach ($groups as $group) {
            echo "<option value='{$group['id_group']}'>" . $group['name'] . "</option>" . PHP_EOL;
        }
        ?>
        </select>
        <div id="bookmark-message"></div>
      </div>
      <div class="modal-footer">
            <input type="button" class="btn btn-primary" value="Add Bookmark" id="modal-bookmark" />
            <input type="submit" class="btn btn-default" value="Close" />
      </div>
      </form>
    </div>
  </div>
</div> 
</div><!-- End of modal bookmark-->