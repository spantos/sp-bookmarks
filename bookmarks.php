<?php
require_once 'config.php';
include 'header.php';
?>
<div class="row">
<div class="col-md-10 col-md-offset-1 col-xs-12">
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<?php
$expanded_group = true;
foreach ($groups as $group) :;
    $id_group = $group['id_group'];
    $group_name = $group['name'];
?>
 <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading-<?php echo $id_group;?>">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?php echo $id_group;?>"
        aria-expanded="<?php echo ($expanded_group) ? 'true' : 'false';?>" aria-controls="collapse-<?php echo $id_group;?>">
          <?php echo $group_name; ?>
        </a>
      </h4>
    <div class="edit-gr">
    <?php
       echo "<a href='javascript:editGroup({$id_group},\"".$group_name."\");' id='edit-group' title='Edit the group name'><span class='glyphicon glyphicon-edit'></span></a>";
       echo "<a href='javascript:deleteGroup({$id_group},\"".$group_name."\");' id='delete-group' title='Delete the group'><span class='glyphicon glyphicon-remove'></span></a>";
    ?>
    </div>
    </div>
    <div id="collapse-<?php echo $id_group; ?>" class="panel-collapse collapse <?php echo ($expanded_group)?'in':''; ?>" role="tabpanel" aria-labelledby="heading-<?php echo $id_group; ?>">
      <div class="panel-body">
      <ul class="list-group">
        <?php
        if (isset($all_bookmarks['id_group'][$id_group])) :
            foreach ($all_bookmarks['id_group'][$id_group] as $bookmark) :
                $url_host = parse_url($bookmark['bookmark_url'], PHP_URL_HOST);
                $description = $bookmark['bookmark_description'];
                $bookmark_name = $bookmark['bookmark_name'];
                $bookmark_id = $bookmark['id_bookmark'];
                echo '<li class="list-group-item">'.PHP_EOL;
                echo "<a href='{$bookmark['bookmark_url']}' target='_blank'>" . $bookmark_name . "</a>" . PHP_EOL;
                echo "<span class='url_host'>{$url_host}</span>" . PHP_EOL;
                echo "<div>" . PHP_EOL;
                echo "<span class='description'>{$description}</span>" . PHP_EOL;
                echo "</div>" . PHP_EOL;
                echo "<div class='bookmark'>" . PHP_EOL;
                echo "<a href='javascript:editBookmark({$bookmark_id});' class='edit-bookmark' title='Edit bookmark'><span>edit</span></a>" . PHP_EOL;
                echo "<span>|</span>" . PHP_EOL;
                echo "<a href='javascript:deleteBookmark({$bookmark_id});' class='delete-bookmark' title='Delete bookmark'><span>delete</span></a>" . PHP_EOL;
                echo "</div>" . PHP_EOL;
                echo '</li>'.PHP_EOL;
            endforeach;
        endif;
        ?>
        </ul>
      </div>
    </div>
  </div>
<?php
     $expanded_group = false;
endforeach;
?>
</div>  
</div>
</div>
<?php
include'footer.php';
?>
