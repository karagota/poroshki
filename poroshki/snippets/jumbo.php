<div class="jumbotron">
	<?php echo $labels['jumbo'];?>
	<ul class="nav nav-pills datefilter">
	<li id="day" <?php if ((isset($_SESSION['from']) && $_SESSION['from']==date('d.m.Y',time()-24*60*60) && $_SESSION['to']==date('d.m.Y'))) echo ' class="active" '; ?> ><a  href="#"   title="24 часа" >Сутки</a></li>
	<li id="week" <?php if  (isset($_SESSION['from']) && $_SESSION['from']==date('d.m.Y',time()-24*60*60*7) && $_SESSION['to']==date('d.m.Y')) echo ' class="active" '; ?> ><a href="#"   title="7 cуток">Неделя</a></li>
	<li id="month" <?php if  (isset($_SESSION['from']) && $_SESSION['from']==date('d.m.Y',time()-24*60*60*30) && $_SESSION['to']==date('d.m.Y')) echo ' class="active" '; ?>><a href="#"   title="30 суток">Месяц</a></li>
	<li id="annual" <?php if  (isset($_SESSION['from']) && $_SESSION['from']==date('d.m.Y',time()-24*60*60*365) && $_SESSION['to']==date('d.m.Y')) echo ' class="active" '; ?>><a href="#"   title="365 суток">Год</a></li>
	<li id="alltime" <?php if (empty($_SESSION['filter'])  || ($_SESSION['from']=='' && $_SESSION['to']=='')) echo ' class="active" '; ?> ><a href="#"   title="С момента первой публикации">Всё время</a></li>
	</ul>
<div id="content"></div>
<div id="lenta"></div>
</div>