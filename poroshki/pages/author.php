<?php
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
$id = (int) $_GET['id'];
$res = mysql_query ("SELECT * from authors where id='".$id ."'");
if (mysql_num_rows($res)==1) $author = mysql_fetch_assoc($res); else die("Такого автора нет");
$visitdate = ru_date('%e %bg %Y', strtotime($author['last']));
$visittime = date('H:i:s', strtotime($author['last']));
?>
<div class="row">
	<div class="col-12" style="padding:0 15px;">
		<div class="media-body">
		  <!-- Nested media object -->
		  <div class="media">
			<a class="pull-left" href="#">

			  <img  class="media-object" data-src="holder.js/64x64/text::-)" src="<?php echo $root_path;?>images/avatars/<?php echo $author['id']; ?>.jpg?ok" />
			</a>
			<div class="media-body">
				<h4 class="media-heading"><?php echo $author['nickname']; if ($user->is_logged() && ($id==$_SESSION['user_id'])) {?> <a href="/profile" class="pull-right btn btn-primary"><?php echo $labels['edit_profile'];?></a> <?php }?></h4>
				<p><?php echo $author['lastname']; ?> <?php echo $author['name']; ?></p>
				
				<p><b><?php echo $labels['author_rating']; ?>:</b> <?php $rating =0; $rat = mysql_query("SELECT rating  from rating where subject_type=0 AND subject_id=".$author['id']);  while ($ratingar = mysql_fetch_assoc($rat)) $rating = $ratingar['rating']; echo round($rating*100);?></p>
				
				<p><b><?php echo $labels['reg_date'];?>:</b> <?php echo cyrillic_date($author['since'],'%d %B %Y');?> г.</p>

				<p><b><?php echo $labels['birth_date'];?>:</b> <?php echo cyrillic_date($author['birthday'],'%d %B');?></p>
				<p><b><?php echo $labels['city'];?>:</b><?php echo $author['city']; ?></p>
				<p><b><?php echo $labels['about_yourself'];?>:</b> <?php echo $author['about']; ?></p>
				<p><b><?php echo $labels['activity'];?>:</b> <?php echo $labels['last_time_was'];?> <?php echo $visitdate;?> г. в <?php echo $visittime;?></p>
				<?php $res_lastpost = mysql_query("SELECT * from articles where status=1 and author_id=".$author['id']." ORDER BY since DESC LIMIT 0,1");
				while ($lastpost = mysql_fetch_assoc($res_lastpost)) {?>
				<p><b><?php echo $labels['last_post'];?> (<?php echo cyrillic_date($lastpost['since']);?> г.): </b><a href="/<?php echo $lastpost['id']?>"><br /><?php echo $lastpost['text'];?></a></p><?php } ?>
				<p><b><a href="/fav/<?php echo $id;?>"><?php echo $labels['favorite'];?>:</a></b> 
				<?php 
					$fav_sql = "SELECT count(1) as num from favorites where author_id=".$id;
					$fav_res = mysql_query($fav_sql);
					while ($fav_row = mysql_fetch_assoc($fav_res)) $fav = $fav_row['num'];
					echo $fav.'  '.$labels['of_articles'];
				?>
				</p>
				<p><b><a href="/own/<?php echo $id;?>"><?php echo $labels['of_own_articles'];?>:</a></b> 
				<?php 
					$own = 0;
					$own_sql = "SELECT count(1) as num from articles where status=1 and author_id=".$id;
					$own_res = mysql_query($own_sql);
					
					while ($own_row = mysql_fetch_assoc($own_res)) $own = $own_row['num'];
					echo $own;
				?>
				</p>
				<p><b>Сегодня опубликовано:</a></b> 
				<?php 
					$pub_sql = "select count(1) as count from articles where author_id=".$author['id']." and status=1 and DATE_FORMAT(since, '%Y-%m-%d') = CURDATE()";
					//echo $pub_sql;
					$pub_res = mysql_query($pub_sql);
					$pub_row = mysql_fetch_assoc($pub_res);
					
					echo  " {$pub_row['count']} ".$labels['of_articles'];
				?>
				
				из  
				<?php 
				$max_publish_temp = get_rating_parameters('max_publish_temp');
				$min_publish_temp = get_rating_parameters('min_publish_temp');
				
			
				if ($author['role']=='editor') {
					$temp = $max_publish_temp;
				}
				else {
					$period = get_rating_parameters('period');
				
					$sql = "select avg(rating) as avg_rating from rating,articles where subject_type=0 and articles.status=1 and articles.author_id=rating.subject_id and articles.since>(Now() - Interval ".$period." Day)";

					$res = mysql_query($sql);
					while($row = mysql_fetch_assoc($res)) $avg_rating = $row['avg_rating'];
					
					$sql = "select rating from rating where subject_type=0 and subject_id=".$author['id'];
					$res = mysql_query($sql);
					while($row = mysql_fetch_assoc($res)) $rating = $row['rating'];
					
					if ($avg_rating==1) $temp = $max_publish_temp;
					elseif ($rating<$avg_rating) $temp = $min_publish_temp;
					else $temp = ceil(($max_publish_temp*($rating - $avg_rating) + $min_publish_temp * (1-$rating))/(1-$avg_rating));
					
					
				}
				echo $temp;
				//$avg_rating = средний рейтинг авторов по всем пишущим авторам
				
				//echo "ceil((max_publish_temp $max_publish_temp*(rating $rating - avg_rating $avg_rating) + min_publish_temp $min_publish_temp * (1-rating $rating))/(1-avg_rating$avg_rating))";
				?>
				</p>
				
			</div>
		  </div>
		</div>
	</div>
</div><!--/row-->