<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
if (isset($_POST['editors_submit'])){
	$editors = $_POST['editors'];
	foreach ($editors as $editor) {
		$cleared_editors[] = (int)$editor;
	}
	$cleared_editors = "(".implode(',',$cleared_editors).")";
	$sql = "UPDATE authors set role=''";
	mysql_query($sql);
     $sql ="UPDATE authors  set role='editor' where id in $cleared_editors";
	echo $sql;
	mysql_query($sql);
	echo '<script type="text/javascript">window.location.href = window.location.href;</script>';
	
}
$sql = "select * from authors order by nickname ASC";
$res = mysql_query($sql);
while ($row=mysql_fetch_assoc($res)){
$authors[]=$row;

}
//print_r($authors);
?>
<h3>Добавить в члены ЛитКоллегии отмеченных авторов</h3>
<br>
<form method="POST" action="/admin/editors" enctype="multipart/form-data">  
<div class="row" >
	<div class="col-12" style="padding:0 15px;width:430px;">
		<ul class="list-group">
		<?php foreach ($authors as $author) { ?>
		<li class="list-group-item">
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" name="editors[]" value ="<?php echo $author['id'];?>" <?php if (isset($author['role']) && $author['role']=='editor') echo 'checked'; ?>/></span><a href="/author/<?php echo $author['id'];?>" class="form-control"><?php echo $author['nickname'];?>(<?php echo $author['name'];?>&nbsp;<?php echo $author['lastname'];?>)</a>
		</div>
		</li>
		<?php }?>
		</ul>
		<button name="editors_submit" type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-primary">Добавить отмеченных авторов</button>
	</div>
</div><!--/row-->
</form>
<br><br><br><br>
<?php } ?>
