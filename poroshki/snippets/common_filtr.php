<?php
$subcat = null;
if (isset ($_GET['subcat'])) 
{
	$_SESSION['subcat'] = $subcat = $_GET['subcat']; 
}
else if (isset($_SESSION['subcat'])) $subcat = $_SESSION['subcat'];
$getcat = null;
if (isset ($_GET['cat'])) 
{
	$_SESSION['cat'] = $getcat = $_GET['cat']; 
}
else if (isset($_SESSION['cat'])) $getcat = $_SESSION['cat'];
$res = mysql_query('select id,parent_id,name from categories');
$cats = array();
while ($cat = mysql_fetch_assoc($res)) {
	$cats[]=$cat;
}
$cathtml="";
$subcathtml="";
foreach ($cats as $cat) {
if ($cat['parent_id']==0) {
	$cathtml.="<option value='".$cat['id']."'";
	if (isset($_SESSION['cat']) && $_SESSION['cat']==$cat['id']) $cathtml.=' selected ';
	$cathtml.=">".$cat['name']."</option>\n";
}
else {
	$subcathtml.= '<option ';
	if ($subcat==$cat['id'] ) $subcathtml.= ' selected ';
	$subcathtml.= 'value="'.$cat['id'].'">'.$cat['name'].'</option>';
}
}
?>
<div class="list-group-item" style="padding-right:0px;">
<form class="filtr dateblock">

	<div class="input-group">
	<input type="text" name="author" class="form-control"  placeholder="<?php echo $labels['filter_author'];?>" value="<?php  if (isset($_SESSION['author'])) echo $_SESSION['author']; ?>"/>
	<span class="input-group-addon clearspan"><a class="clear" href="#"><span class="glyphicon glyphicon-remove <?php if (!empty($_SESSION['author'])) echo ' active';?>"></span></a></span>
	</div>
	<br />
	<div class="input-group">
	<input type="text" name="text" class="form-control"  placeholder="<?php echo $labels['filter_text'];?>" value="<?php  if (isset($_SESSION['text'])) echo $_SESSION['text']; ?>" />
	<span class="input-group-addon clearspan"><a class="clear" href="#"><span class="glyphicon glyphicon-remove <?php if (!empty($_SESSION['text'])) echo ' active';?>"></span></a></span>
	</div>
	<br />
	<div class="input-group">
	<span class="input-group-addon"><?php echo $labels['from'];?>:&nbsp;&nbsp;</span>
	<input type="text" class="datepicker form-control <?php if (!empty($_SESSION['from'])) echo "active";?>" id="from" name="from" value="<?php if (isset($_SESSION['from'])) echo $_SESSION['from']; else echo '';?>" placeholder="<?php echo $labels['date_from'];?>"/> 
	<span class="input-group-addon clearspan"><a class="clear" href="#"><span class="glyphicon glyphicon-remove resetsgray <?php if (!empty($_SESSION['from'])) echo ' active';?>" ></span></a></span>
	</div>
	<br />
	<div class="input-group">
	<span class="input-group-addon"><?php echo $labels['to'];?>:</span>
	<input type="text" class="datepicker  form-control  <?php if (!empty($_SESSION['to'])) echo "active";?>" id="to"  name="to" value="<?php if (isset($_SESSION['to'])) echo $_SESSION['to']; else echo '';?>" placeholder="<?php echo $labels['date_to'];?>" />
	<span class="input-group-addon clearspan"><a class="clear" href="#"><span class="glyphicon glyphicon-remove resetsgray <?php if (!empty($_SESSION['to'])) echo ' active';?>" ></span></a></span>
	</div>
	<br />
	<?php if($user->is_logged()) { ?>
	<div class="input-group">
	<select class="form-control" id="checked" name="checked" <?php if ($_SESSION['checked']>0) echo 'style="color:black;"'; ?> >
		<option <?php if ($_SESSION['checked']===0) {?> selected <?php }?> value="0"><?php echo $labels['all'];?></option>
		<?php if (isset($_SESSION['user_id'])){ ?>
		<option <?php if ($_SESSION['checked']==1) {?> selected <?php }?> value="1"><?php echo $labels['unwatched'];?></option>
		<option <?php if ($_SESSION['checked']==2) {?> selected <?php }?> value="2"><?php echo $labels['unrated'];?></option>
		<?php } ?>
	</select>
	 <span class="input-group-addon clearspan"><a class="clear" href="#"><span class="glyphicon glyphicon-remove <?php if ($checked>0) echo ' active';?>"></span></a></span>
	</div>
	<br />
	<?php } ?>
	<div style="text-align:right;height:34px;line-height:34px;margin-bottom:-10px;">
		<a href="#"  title="<?php echo $labels['clear_all'];?>" onclick="$('.filtr')[0].reset();$('.filtr .form-control').prop('selectedIndex', 0).css('color','#999');$('.datepicker').val('');$('.resetsgray,.clear').removeAttr('style').find('span').removeClass('active');$('.datefilter li').removeClass('active');$('#alltime').addClass('active'); $('#filtr').text('Фильтр');$('.filtr input[type=\'text\']').val('');$('.row').last().load('?'+$('form.filtr').serialize()+' .pagecontent', function() {$('.more').first().contents().unwrap();$('.pagecontent .pagecontent').first().contents().unwrap();window.location = window.location.href.split('?')[0];});" style="margin-right:12px;" ><span class="glyphicon glyphicon-trash"></span></a>
		<p></p>
	</div>
</form>
</div>