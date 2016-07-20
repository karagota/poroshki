<?php
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
if (!isset($_SESSION['user_id']) || !isset($user)) die('<a href="/signin">Пожалуйста, залогиньтесь</a>');
$id = $_SESSION['user_id'];
$res = mysql_query ("SELECT * from articles where status=0 and id > 1 and author_id='".$id ."' ORDER BY last DESC");
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
		<h2><?php echo $labels['drafts']; ?></h2>
		<?php if (sizeof($favs)==0) echo 'Пока ничего нет'; else {?>
		<?php /*<ol style="margin-top:40px;" class="fav-list"> */ ?>
		<?php foreach ($favs as $fav)
			{
				$favdate = date("d.m.y", strtotime($fav['last']));
				$favtime = date('H:i:s', strtotime($fav['last']));
				$edit = '';
				if ($user->is_logged() && ($fav['author_id']==$_SESSION['user_id'])) {$edit='create/';}
				//echo '<li style="margin-bottom:20px;">';
				if ($user->is_logged() && ($fav['author_id']==$_SESSION['user_id'])) {
				echo '<div class="draft"><div style="float:left;"><a href="#" style="color:#BBB;" class="delete-article" id="id-'.$fav['id'].'" title="Удалить"><span class="glyphicon glyphicon-remove"></span></a>&nbsp;&nbsp;&nbsp;</div>';
			}
				echo '<div style="float:left;">';
				echo '<a href="/'.$edit.''.$fav['id'].'">'.$fav['text'].'</a>';
				echo '</div><div style="clear:both;"></div><br/></div>';
			}
			
		?>
		<?php /*</ol>*/ ?>
		<?php } ?>
	</div>
</div><!--/row-->