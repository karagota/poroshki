	<?php 
	$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

	if (!isset($_SESSION['user_id'])) echo $labels['please_authorise'].': <a href="/signin">'.$labels['do_login'].'</a>';

	else if (!$_SESSION['invite_sent_for_good']){
	    
		$invites = array();
		$sql = "select * from invites where author_id=".$_SESSION['user_id'];
		$res = mysql_query($sql);
		while($row = mysql_fetch_assoc($res)) $invites[]=$row;
		$sql = "select sum(invites) as invites_add from author_invites where author_id=".$_SESSION['user_id'];
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$invites_add=$row['invites_add'];
		$invites_rest = $user->invites + $invites_add - sizeof($invites);
		if ($invites_rest==0) echo $labels['no_more_invites'];
		else { ?>
	<h1><?php echo $labels['invite_header'];?></h1>
	<br />
	<?php echo $_SESSION['invite_error']; ?>
	<div style="width:300px;">
        <!--<form action="javascript:void(0);">-->
		<form method="post">
         		  
          <div class="control-group">
            
            <div class="controls">
              <input class="form-control" placeholder="<?php echo $labels['invitee_email'];?>" type="text" title="<?php echo $labels['invitee_email'];?>" id="signin-email1" name="invite_email1">
            </div>
          </div>
		  
		   <br>
		  
			   
		  <div class="control-group">
            
            <div class="controls">
              <textarea class="form-control" name="text_invite" title="<?php echo $labels['invitee_text'];?>"><?php echo $labels['invitee_text_content'];?></textarea>
            </div>
          </div>
		  
		   <br>
		   
	  
          <div>
		  <br>
            <div class="control-group">
              <div class="controls auth-signin-box">
                <button type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-success"><?php echo $labels['send_invite']; ?></button>
                <button class="btn hide"><img src="https://d2wvvaown1ul17.cloudfront.net/site-static/images/icons/loading.gif"></button>
                </div>
            </div>
          </div>
        </form>
	<br />
	</div>
	<div style="width:100%;">
	<?php if ($invites_rest==1) {?><p><?php echo $labels['you_have_1_invite'];?></p><?php }  else {?>
	
	<p><?php echo $labels['you_have_invites'];?> <?php echo $invites_rest ?> <?php echo $labels['of_invites'];?>.</p> <?php }?>
		<?php if (sizeof($invites)>0) { ?>
			<p><?php echo $labels['You_have_already_sent'];?> <?php echo sizeof($invites);?> <?php echo $labels['of_invites'];?>:
			<ul>
			<?php foreach ($invites as $invite) { ?>
				<li><?php 
				if ($invite['invited_author_id']!=0) echo '<a href="/author/'.$invite['invited_author_id'].'">';
				echo $invite['email'];
				if ($invite['invited_author_id']!=0) echo '</a>';
				echo ' '.ru_date('%e %bg %Y ', strtotime($invite['sent'])).' г.'; ?>
				<?php if ($invite['invited_author_id']!=0) echo '('.$labels['invite_used_when'].' '.ru_date('%e %bg %Y ', strtotime($invite['used'])).' г.)';?>
				</li>
			<?php } ?>
			</ul>
		</p>
		<?php } ?>
    </div>

<?php }} else {
	echo $_SESSION['invite_success'];
	$_SESSION['invite_sent_for_good']=false;
} ?>

