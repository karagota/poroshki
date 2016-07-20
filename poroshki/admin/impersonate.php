<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
//print_r($_SESSION);
if (isset($_POST['imp_submit'])){
	$_SESSION['user_id']=(int)$_POST['author'];
	$user->authorize();
	echo '<script type="text/javascript">location.href="/";</script>';
}
$sql = "select * from authors a where id <> ".$_SESSION['user_id']." order by nickname ASC";
$res = mysql_query($sql);
while ($row=mysql_fetch_assoc($res)){
$authors[]=$row;

}
//print_r($authors);
?>
<form method="POST" action="/admin/impersonate" enctype="multipart/form-data">  
<div class="row" >
	<div class="col-12" style="padding:0 15px;width:330px;">
		<ul class="list-group">
		<?php foreach ($authors as $author) { ?>
		<li class="list-group-item">
		<div class="input-group">
			<span class="input-group-addon">
				<input type="radio" name="author" value ="<?php echo $author['id'];?>" <?php if ($author['id']==$_SESSION['user_id']) echo ' checked';?>/></span><a href="/author/<?php echo $author['id'];?>" class="form-control"><?php echo $author['nickname'];?>(<?php echo $author['name'];?>&nbsp;<?php echo $author['lastname'];?>)</a>
		</div>
		</li>
		<?php }?>
		</ul>
		<button name="imp_submit" type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-primary">Перелогиниться</button>
	</div>
</div><!--/row-->
</form>
<br><br><br><br>

<?php } ?>