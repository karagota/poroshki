<?php
if (!isset($_GET['code']) ) {
?>
<div style="width:300px;">
<?php if (isset($_GET['wrongcode'])) {echo '<p>'.$labels['wrongcode'].'.</p>';} ?>
<form action="/register">
  <div class="control-group">
	<div class="controls">
	  <input class="form-control" placeholder="<?php echo $labels['enter_code'];?>" type="text" title="<?php echo $labels['code'];?>" id="signin-code" name="code">
	</div>
  </div>
   <div>
	  <br>
		<div class="control-group">
		  <div class="controls auth-signin-box">
			<button type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-success"><?php echo $labels['send_code'];?></button>
			
			</div>
		</div>
	  </div>
</form>
</div>
<?php } else { 
$invite=array();
$sql = "select * from `invites` where code='".$_GET['code']."'";
//echo $sql;
	$res = mysql_query($sql);
	while($row=mysql_fetch_assoc($res)) {
		$invite[]=$row;
	}
	//print_r($invite);
	//echo 'size of invite='.sizeof($invite);
	if (sizeof($invite)==1 && $invite[0]['invited_author_id']==0) {
		session_start();
		$_SESSION['code']=$invite[0]['code'];
		$_SESSION['user_email']=$invite[0]['email'];
		//создать такого пользователя в БД с таким емейлом
		//сохранить его сессионный ключ
		

?>
<div class="col-xs-9" style="border-right:#DDD 1px solid;">
<p><?php echo $labels['dear_user'];?> <?php echo $invite[0]['email']; ?>! <?php echo $labels['to_create_author'];?> <?php echo $sitename; ?>, <?php echo $labels['choose_social_network']; ?>:<br><br></p>
<div style="width:300px;height:500px;">
<?php /*<script src="//ulogin.ru/js/ulogin.js"></script><div id="uLogin1" data-uloginid="a32fc3ee"></div>*/?>
<script src="//ulogin.ru/js/ulogin.js"></script><div id="uLogin_0b7d0f90" data-uloginid="0b7d0f90"></div>
  </div>
</div>
<?php }
else echo '<script>location.href="'.$domain.'/register?wrongcode";</script>';
} ?>