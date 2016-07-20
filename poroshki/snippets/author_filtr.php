<?php
function has_access() {
global $user;
	return (isset($_SESSION['user_id']) && (empty($_GET['id']) || $_SESSION['user_id']==$_GET['id'] || ($_GET['inc']=='create' && $user->is_author(1,$_GET['id']))));

} 
$id = ($_GET['inc']!='create' && isset($_GET['id']))?(int)$_GET['id']:$_SESSION['user_id'];
$res = mysql_query ("SELECT * from authors where id='". $id."'");
while ($row=mysql_fetch_assoc($res)) $author = $row;
$res = mysql_query ("SELECT count(1) as num from articles where status=1 and id>1 and author_id='". $id."'"); 
while ($row=mysql_fetch_assoc($res)) $author['num'] = $row['num'];
$res = mysql_query ("SELECT count(1) as drafts from articles where status=0 and id>1 and author_id='". $id."'");
while ($row=mysql_fetch_assoc($res)) $author['drafts'] = $row['drafts'];
$res = mysql_query ("SELECT count(1) as fav from favorites where author_id='". $id."'");
while ($row=mysql_fetch_assoc($res)) $author['fav'] = $row['fav'];
$sql = "select count(1) as num from invites where author_id=".$_SESSION['user_id'];
$res = mysql_query($sql);
while($row = mysql_fetch_assoc($res)) $num_invites=$row['num'];
$sql = "select sum(invites) as sum from author_invites where author_id=".$_SESSION['user_id'];
$res = mysql_query($sql);
while($row = mysql_fetch_assoc($res)) $invites_add=$row['sum'];
$invites_rest = $user->invites +$invites_add - $num_invites;
?>
<div class="list-group-item">
	<h3><a href="/author/<?php echo $author['id']; ?>"><?php echo $author['nickname']; ?></a></h3>
	<p><a href='/own/<?php echo $author['id']; ?>'><?php echo $author['num'];?> <?php echo $labels['of_own_articles'];?></a></p>
	<?php if (has_access()) { ?><p><a href='/drafts'><?php echo $author['drafts'];?> <?php echo $labels['of_drafts'];?></a></p> <?php } ?>
	<p><a href='/fav/<?php echo $author['id']; ?>'><?php echo $author['fav'];?> <?php echo $labels['of_favoured_articles'];?></a></p>
	
	<?php if (has_access()) { ?><p><a href="/invite"><?php echo $invites_rest;?> <?php echo $labels['of_invites'];?></a></p><?php } ?>
</div>
