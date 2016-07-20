<?php
require('../config.php');

$server_time_zone='Europe/Moscow';

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
mysql_set_charset('utf8');
mb_internal_encoding("utf8");
date_default_timezone_set( $server_time_zone );

//Извлекаем значение Максимального Интервала Публикаций
$per_sql="select value from rating_scalar_params where name='period'";
$per_res=mysql_query($per_sql);
while ($per_row = mysql_fetch_assoc($per_res)) $period=$per_row['value'];
//echo 'period='.$period.'<br>';

//ДУСА
$top_sql="select value from rating_scalar_params where name='author_top'";
$top_res=mysql_query($top_sql);
while ($top_row = mysql_fetch_assoc($top_res)) $top=$top_row['value'];
//echo 'top='.$top.'<br>'; 

$event_date = date("Y-m-d H:i:s");

$sql="select id from authors ORDER BY id ASC"; 
$res = mysql_query($sql);
while ($row = mysql_fetch_assoc($res)) {
	$new_rating = 0; 
	$ini_sql = "select new_rating from log_rating where subject_type=0 and subject_id=".$row['id']." and event_date < '".$event_date."' and event_type=2";
	echo $ini_sql.'<br>';
	$ini_res = mysql_query($ini_sql);
	$ini_row = mysql_fetch_assoc($ini_res);
	$ini_rating = $ini_row['new_rating'];
	echo 'ini_rating='.$ini_rating.'<Br>';

	$all_articles_sql ="SELECT d.subject_id, d.subject_type, d.event_date, d.new_rating as rating FROM log_rating d INNER JOIN (
	  SELECT subject_id, subject_type, max(event_date) AS ev_date
	  FROM log_rating d
	  where event_date <  '".$event_date."' 
		and subject_type=1
		and author_id='".$row['id']."'
	  GROUP BY subject_id, subject_type
	  ) a ON a.subject_id= d.subject_id AND a.subject_type = d.subject_type
	 and a.ev_date = d.event_date
	 and d.author_id='".$row['id']."'
	order by rating DESC";
	echo $all_articles_sql.'<br>';
	$all_articles_res = mysql_query($all_articles_sql);
	$all_articles=array();
	while ($all_articles_row = mysql_fetch_assoc($all_articles_res)) {
		$all_articles[]=$all_articles_row;
	}

	$top_size = round($top*sizeof($all_articles));
	if ($top_size<1 && sizeof($all_articles)>0) $top_size = 1;
	$new_articles = array_slice($all_articles,0,$top_size);


	if (sizeof($new_articles)==0) $new_rating = $ini_rating;
	else {
		$amount = sizeof($new_articles);
		$sum = 0;
		foreach ($new_articles as $article)
		{	
			$sum += $article['rating'];
		}
		
		$C = $sum/(2*$amount) + 0.5;
		
		$new_rating = $C + $a_cor_weight * ($ini_rating - $C)/ sqrt($amount);
	}
	 

	$update_sql = "update rating set rating=$new_rating where subject_type=0 and subject_id=".$row['id'];
	echo $update_sql.'<br>';
	mysql_query($update_sql); 
	
	$update_sql = "insert into log_rating (id, event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) values (NULL,  '".$event_date."', 5, ".$row['id'].", 0, NULL, 0,0, $new_rating)";
	echo $update_sql.'<br>';
	mysql_query($update_sql);
}
echo 'ok';
?>