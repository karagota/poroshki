<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
if (isset($_POST['admins_submit'])){
	$admins = $_POST['admins'];
	foreach ($admins as $admin) {
		$cleared_admins[] = (int)$admin;
	}
	$cleared_admins = "(".implode(", 1), (",$cleared_admins).",1)";
	$sql = "DELETE FROM user_group where group_id=1";
	mysql_query($sql);
     $sql ="INSERT INTO user_group (user_id, group_id) values $cleared_admins";
	 //echo $sql;
	mysql_query($sql);
	echo '<script type="text/javascript">window.location.href = window.location.href;</script>';
}
$sql = "select a.*, ug.group_id from authors a LEFT JOIN user_group ug on a.id=ug.user_id order by a.nickname ASC";
$res = mysql_query($sql);
while ($row=mysql_fetch_assoc($res)){
$authors[]=$row;

}
//print_r($authors);
?>
<form method="POST" action="/admin/admins" enctype="multipart/form-data">  
<div class="row" >
	<div class="col-12" style="padding:0 15px;width:330px;">
		<ul class="list-group">
		<?php foreach ($authors as $author) { ?>
		<li class="list-group-item">
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" name="admins[]" value ="<?php echo $author['id'];?>" <?php if (isset($author['group_id']) && $author['group_id']=='1') echo 'checked'; ?>/></span><a href="/author/<?php echo $author['id'];?>" class="form-control"><?php echo $author['nickname'];?>(<?php echo $author['name'];?>&nbsp;<?php echo $author['lastname'];?>)</a>
		</div>
		</li>
		<?php }?>
		</ul>
		<button name="admins_submit" type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-primary">Сделать админами отмеченных авторов</button>
	</div>
</div><!--/row-->
</form>
<br><br><br><br>
<?php } ?>
