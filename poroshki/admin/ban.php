<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {


if (isset($_POST['ban_submit'])){
	$bans = $_POST['bans'];
	foreach ($bans as $ban) {
		$cleared_bans[] = (int)$ban;
	}
	$cleared_bans = "(".implode(", ".$_SESSION['user_id']."), (",$cleared_bans).",".$_SESSION['user_id'].")";
	//print_r($cleared_bans);
	mysql_query("DELETE from bans");
     $sql ="INSERT INTO bans (author_id, admin_id) values $cleared_bans";
	 //echo $sql;
	mysql_query($sql);
	echo '<script type="text/javascript">window.location.href = window.location.href;</script>';
}
$sql = "select a.*, bans.author_id from authors a LEFT JOIN bans on a.id=bans.author_id order by a.nickname ASC";
$res = mysql_query($sql);
while ($row=mysql_fetch_assoc($res)){
$bans[]=$row;

}
//print_r($bans);
?>

<form method="POST" action="/admin/ban" enctype="multipart/form-data">  
<div class="row" >
<p>Галочками отмечены забаненные авторы. Если автор забанен, он не может залогиниться в свой аккаунт.</p>
	<div class="col-12" style="padding:0 15px;width:330px;">
		<ul class="list-group">
		<?php foreach ($bans as $ban) { ?>
		<li class="list-group-item">
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" name="bans[]" value ="<?php echo $ban['id'];?>" <?php if (isset($ban['author_id'])) echo 'checked'; ?>/></span><a href="/author/<?php echo $ban['id'];?>" class="form-control"><?php echo $ban['nickname'];?>(<?php echo $ban['name'];?>&nbsp;<?php echo $ban['lastname'];?>)</a>
		</div>
		</li>
		<?php }?>
		</ul>
		<button name="ban_submit" type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-primary">Забанить отмеченных авторов</button>
	</div>
</div><!--/row-->
</form>
<br><br><br><br>

<?php } ?>