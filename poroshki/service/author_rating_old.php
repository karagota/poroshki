<?php
require('../config.php');

$server_time_zone='Europe/Moscow';

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
mysql_set_charset('utf8');
mb_internal_encoding("utf8");
date_default_timezone_set( $server_time_zone );

//ВПОА
$cor_sql="select value from rating_scalar_params where name='author_cor_weight'";
$cor_res=mysql_query($cor_sql);
while ($cor_row = mysql_fetch_assoc($cor_res)) $cor_weight=$cor_row['value'];

echo 'cor_weight='.$cor_weight.'<br>';

//ДУСА
$top_sql="select value from rating_scalar_params where name='author_top'";
$top_res=mysql_query($top_sql);
while ($top_row = mysql_fetch_assoc($top_res)) $top=$top_row['value'];

echo 'dusa='.$top.'<br>';

$sql = "select authors.id, initial_rating from authors,rating where subject_type=0 and subject_id=authors.id";
echo $sql.'<br>';
$res = mysql_query($sql);
while ($row = mysql_fetch_assoc($res)) {
	$author_id = $row['id'];
	$ini_rating = $row['initial_rating'];
	echo "author_id=$author_id; ini_rating=$ini_rating<br>";
	$n_sql = "select count(1) as n from articles where author_id=$author_id";
	$n_res = mysql_query($n_sql);
	$n_row = mysql_fetch_assoc($n_res);
	$n = $n_row['n'];
	$dusa = round($n*$top);
	$rating_sql = "select sum(rating+1)/(2*count(1)) + $cor_weight*($ini_rating-sum(rating+1)/(2*count(1)))/sqrt(count(1)) as new_rating from articles, rating where rating.subject_type=1 and articles.author_id = $author_id and rating.subject_id = articles.id order by rating DESC limit 0, $dusa";
	echo $rating_sql.'<br>';
	$rating_res = mysql_query($rating_sql);
	$new_rating_row = mysql_fetch_assoc($rating_res);
	$new_rating=$new_rating_row['new_rating'];
	mysql_query("update rating set rating = $new_rating where subject_type=0 and subject_id=$author_id");
	echo  "update rating set rating = $new_rating where subject_type=0 and subject_id=$author_id".'<br>';
}


?>