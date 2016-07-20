<div class="jumbotron">
	<?php echo $labels['jumbo'];?>
	<ul class="nav nav-pills">
	<li <?php if (empty($_GET['order']) || $_GET['order']=='rating' ) echo 'class="active"'; ?>><a href="/authors?order=rating"><?php echo $labels['author_rating'];?></a></li>
	<li <?php if ($_GET['order']=='lastname') echo 'class="active"'; ?>><a href="/authors?order=lastname"><?php echo $labels['last_name'];?></a></li>
	<li <?php if ($_GET['order']=='nickname') echo 'class="active"'; ?>><a href="/authors?order=nickname"><?php echo $labels['nickname'];?></a></li>
	</ul>
</div>