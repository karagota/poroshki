<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
if (isset($_POST['invites_submit'])){
	$invites_add = (int)$_POST['invites_add'];
	if ($invites_add==0) $invites_add=1;
	$users = $_POST['users'];
	foreach ($users as $user) {
		$cleared_users[] = (int)$user;
	}
	$cleared_users = "(".implode(", ".$invites_add.", ".$_SESSION['user_id']."), (",$cleared_users).",".$invites_add.", ".$_SESSION['user_id'].")";
	
	if ($invites_add>0) {
		$sql = "insert into author_invites (author_id, invites,adder) values $cleared_users";
		//echo $sql;
		mysql_query($sql);
		echo $labels['invites_added'];
	}
	
} else {
$sql = "select * from authors order by nickname ASC";
$res = mysql_query($sql);
while ($row=mysql_fetch_assoc($res)){
$authors[]=$row;

}
//print_r($authors);
?>
<form method="POST" action="/admin/invites" enctype="multipart/form-data">  
<div class="row" >
	<div class="col-12" style="padding:0 15px;width:330px;">
		<input name="invites_add" type="text" class="form-control" placeholder="<?php echo $labels['how_much_invites'];?>" />
		<br />
		<ul class="list-group">
		<?php foreach ($authors as $author) { ?>
		<li class="list-group-item">
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" name="users[]" value ="<?php echo $author['id'];?>" /></span><a href="/author/<?php echo $author['id'];?>" class="form-control"><?php echo $author['nickname'];?>(<?php echo $author['name'];?>&nbsp;<?php echo $author['lastname'];?>)</a>
		</div>
		</li>
		<?php }?>
		</ul>
		<button name="invites_submit" type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-primary"><?php echo $labels['add_invites_to_selected_authors'];?></button>
	</div>
</div><!--/row-->
</form>
<br><br><br><br>

<?php } ?>
<?php } ?>