<?php if ($user->is_logged()) {$username = $_SESSION['nickname']; $user_id = $_SESSION['user_id']; if (empty($user_id)) {$sql = "SELECT author_id from oauth_users where uid=".$_SESSION['uid']; $res = mysql_query($sql); while ($row = mysql_fetch_assoc($res)) $user_id = $_SESSION['user_id'] = $row['author_id']; }}
$menu=array('home'=>array('articles'),'author'=>array('author','invite','fav','own'),'create'=>array('create'),'signin'=>array('signin'),'authors'=>array('authors'),'admin'=>array('admin','rating','ban','about','impersonate','invites','tesaurus','history'),'register'=>array('register'),'about'=>array('about'),'wishes'=>array('wishes'),'old'=>array('old1','old2','old3','old4','old5'));
foreach ($menu as $item=>$elements) {
if (in_array($page,$elements))
{
	if (sizeof($elements)==1) $menu_active[$item]='class="active"'; else $menu_active[$item]='active';
}
else $menu_active[$item]='';
}

?>   
   <div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only"><?php echo $labels['navigation'];?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
  		  
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            
            <li <?php echo $menu_active['home'];?>><a  href="/"><span class="glyphicon glyphicon-home"></span></a></li>
			<?php if (isset($username)) { ?>
				<li class="dropdown <?php echo $menu_active['author'];?>" >
				  <a href="#" class="dropdown-toggle " data-toggle="dropdown"><?php echo $username;?> <b class="caret" ></b></a>
				  <ul class="dropdown-menu">
					  <li><a href="/author/<?php echo $user_id; ?>"><?php echo $labels['profile'];?></a></li>
					  <li><a href="/fav/<?php echo $user_id; ?>"><?php echo $labels['favorite'];?></a></li>
					  <li><a href="/own/<?php echo $user_id; ?>"><?php echo $labels['articles'];?></a></li>
					  <li><a href="/drafts"><?php echo $labels['drafts'];?></a></li>
					  <li><a href="/invite"><?php echo $labels['invite'];?></a></li>
					  <li class="divider"></li>
					 <li><a href="/signout"><?php echo $labels['logout'];?></a></li>
				  </ul>
				</li>
				<li  <?php echo $menu_active['create'];?>><a href="/create"><?php echo $labels['create'];?></a></li>
			<?php } else {?>
					<li <?php echo $menu_active['signin'];?>><a href="/signin"><?php echo $labels['login'];?></a></li>
					<?php /*<li <?php echo $menu_active['register'];?>><a href="/register">Вход по приглашению</a></li>*/ ?>
			<?php }?>
          
		
			<li  <?php echo $menu_active['authors'];?>><a href="/authors"><?php echo $labels['authors'];?></a></li>
			<?php if ($user->is_logged() && $user->hasRight('admin')) { ?>
			<li class="dropdown <?php echo $menu_active['admin'];?>">
				  <a href="#" class="dropdown-toggle " data-toggle="dropdown"><?php echo $labels['admin_place'];?><b class="caret" ></b></a>
					<ul class="dropdown-menu">
						<li><a href="/admin/rating"><?php echo $labels['rating'];?></a></li>
						<li><a href="/admin/history"><?php echo $labels['history'];?></a></li>
						<?php /*<li><a href="/admin/admins">Админы</a></li>*/ ?>
						<li><a href="/admin/invites"><?php echo $labels['add_invites'];?></a></li>
						<li><a href="/admin/ban"><?php echo $labels['ban'];?></a></li>
						<li><a href="/admin/impersonate"><?php echo $labels['login_as'];?></a></li>
						<li><a href="/admin/about"><?php echo $labels['edit_about'];?></a></li>
						<li><a href="/admin/wishes"><?php echo $labels['edit_wishes'];?></a></li>
						<li><a href="/admin/editors"><?php echo $labels['editor'];?></a></li>
						<li><a href="/admin/tesaurus">Термины</a></li>
					</ul>
			</li>
			<?php } ?>
			<li  <?php echo $menu_active['about'];?>><a href="/about"><?php echo $labels['about'];?></a></li>
			<?php if ($user->is_logged()) {?><li  <?php echo $menu_active['wishes'];?>><a href="/wishes"><?php echo $labels['wishes'];?></a></li> <?php } ?>
			  <li class="dropdown <?php echo $menu_active['old'];?>">
				  <a href="#" class="dropdown-toggle " data-toggle="dropdown"><?php echo $labels['old'];?><b class="caret" ></b></a>
				  <ul class="dropdown-menu">
					  <?php if (!empty($labels['old1']) && !empty($labels['old1_href'])) {?><li><a href="<?php echo $labels['old1_href'];?>" target="_blank"><?php echo $labels['old1'];?></a></li> <?php } ?>
					   <?php if (!empty($labels['old2']) && !empty($labels['old2_href'])) {?><li><a href="<?php echo $labels['old2_href'];?>" target="_blank"><?php echo $labels['old2'];?></a></li><?php } ?>
					   <?php if (!empty($labels['old3']) && !empty($labels['old3_href'])) {?><li><a href="<?php echo $labels['old3_href'];?>" target="_blank"><?php echo $labels['old3'];?></a></li><?php } ?>
					   <?php if (!empty($labels['old4']) && !empty($labels['old4_href'])) {?><li><a href="<?php echo $labels['old4_href'];?>" target="_blank"><?php echo $labels['old4'];?></a></li><?php } ?>
					   <?php if (!empty($labels['old5']) && !empty($labels['old5_href'])) {?><li><a href="<?php echo $labels['old5_href'];?>" target="_blank"><?php echo $labels['old5'];?></a></li><?php } ?>
				  </ul>
			  </li>
          </ul>
		  
		<form class="navbar-form navbar-right"  action="/" role="search" >
		<span style="color:#999;padding-left:20px;">18+&nbsp;&nbsp;&nbsp;</span>
        <div class="form-group">
          <input type="text" name="text" class="form-control" placeholder="Поиск" style="min-width:217px;">
        </div>
        <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
      </form>
        </div><!-- /.nav-collapse -->
		
      </div><!-- /.container -->
	  
    </div><!-- /.navbar -->