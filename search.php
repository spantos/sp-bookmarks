<?php
require_once 'config.php';
include 'header.php';
if (!isset($_SESSION['logged_user_id']) || !$session->verifySession($_SESSION['token'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
if (!isset($_POST['search'])) {
    header('location:bookmarks.php');
    exit();
}
$id_user = $_SESSION['logged_user_id'];
$search_text = $_POST['search'];
?>
<div class="row">
    <div class="col-md-10 col-xs-12">
        <h2>Search results for: <?php echo $search_text; ?></h2>
<?php
if (empty($search_text)) {
    echo '<span>Please enter search text.</span>';
}
if ($search_text) :
    $search_bookmarks = new BookmarksQuery();
    $search_results = $search_bookmarks->bookmarksSearch($id_user, $search_text);
    if ($search_results && ($search_results != 'error')) :
?>            
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading-search">
      <h4 class="panel-title">Search results</h4>
    </div>
    <div id="collapse-search" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading-search">
      <div class="panel-body">
      <ul class="list-group">
<?php
for ($i=0,$n=count($search_results); $i<$n; $i++) :
    foreach ($search_results[$i] as $search_data) :
        $url_host = parse_url($search_data['bookmark_url'], PHP_URL_HOST);
        $description = $search_data['bookmark_description'];
        $bookmark_name = $search_data['bookmark_name'];
        $bookmark_id = $search_data['id_bookmark'];
        echo '<li class="list-group-item">'.PHP_EOL;
        echo "<a href={$search_data['bookmark_url']} target='_blank'>" . $bookmark_name . "</a>" . PHP_EOL;
        echo "<span class='url_host'>{$url_host}</span>" . PHP_EOL;
        echo "<div>" . PHP_EOL;
        echo "<span class='description'>{$description}</span>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
        echo '</li>'.PHP_EOL;
    endforeach;
endfor;
  
?>
        </ul>
      </div>
    </div>
  </div>

<?php
    elseif ($search_results === 'error') :
        echo 'An error has occurred. Try later.';
    else :
        echo '<p class="bg-warning lead">Nothing was found.</p>';
    endif;
endif;
?>
    </div>
</div>
<?php
include 'footer.php';
?>
