<?php
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."infobar.php");
$id = (int) $_GET['id'];
$res = mysql_query ("SELECT * from articles where status=1 and author_id='".$id ."' and id>1 ORDER BY last DESC");
while ($favs_row = mysql_fetch_assoc($res)) $favs[]=$favs_row;
$res = mysql_query ("SELECT * from authors where id='".$id ."'");
while ($auth_row = mysql_fetch_assoc($res)) $author = $auth_row;
?>

<div class="row">
	<div class="col-12" style="padding:0 15px;">
		
		<div class="media">
			<a class="pull-left" href="#">
			 
			   <img  class="media-object" data-src="holder.js/64x64/text::-)" src="<?php echo $root_path;?>images/avatars/<?php echo $author['id']; ?>.jpg?ok"  />
			</a>
			<div class="media-body">
				<h4 class="media-heading"><a href="/author/<?php echo $author['id']; ?>" ><?php echo $author['nickname']; ?></a></h4>
				<p><?php echo $author['lastname']; ?> <?php echo $author['name']; ?></p>
				
			</div>
		</div>
		<h2><?php echo $labels['articles']; ?></h2>
		<?php if (sizeof($favs)==0) echo 'Пока ничего нет'; else {?>
		<?php /*<ol style="margin-top:40px;" class="fav-list">*/ ?>
		<?php foreach ($favs as $fav)
			{
			  if (empty($fav['title'])) $fav['title'] = $fav['text'];
				$favdate = date("d.m.y", strtotime($fav['last']));
				$favtime = date('H:i:s', strtotime($fav['last']));
				
				if ($user->is_logged() && ($fav['author_id']==$_SESSION['user_id'])) {
					echo '<div style="float:left;"><a href="#" style="color:#BBB;" class="change-status" id="id-'.$fav['id'].'" title="Убрать статью в черновики"><span class="glyphicon glyphicon-remove"></span></a>&nbsp;&nbsp;&nbsp;</div>';
				}
	
				echo '<div style="float:left;"><a href="/'.$fav['id'].'">'.$fav['text'].'</a><p></p>';
				echo infobar($fav,$user,1,$vote=0,$info=1);
				echo '</div><div style="clear:both;"></div><br />';

			}
			
		?>
		<?php /*</ol>*/ ?>
		<?php } ?>
	</div>
</div><!--/row-->