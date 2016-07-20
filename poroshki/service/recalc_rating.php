<?php
require('../config.php');

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
mysql_set_charset('utf8');
mb_internal_encoding("utf8");
date_default_timezone_set( $server_time_zone );
echo 'begin...<br>';

//устанавливаем рейтинг всех авторов в 0.5, начальный рейтинг всех авторов в 0.5
mysql_query("update rating set rating=0.5, initial_rating=0.5 where subject_type=0");
echo "update rating set rating=0.5, initial_rating=0.5 where subject_type=0";
echo "<br>";

//Устанавливаем рейтинг всех статей в 0, начальный рейтинг всех статей в 0
mysql_query("update rating set rating=0, initial_rating=0 where subject_type=1");
echo "update rating set rating=0, initial_rating=0 where subject_type=1";
echo "<br>";

//Извлекаем значение Веса Поправки Оценки Статьи
$cor_sql="select value from rating_scalar_params where name='article_cor_weight'";
$cor_res=mysql_query($cor_sql);
while ($cor_row = mysql_fetch_assoc($cor_res)) $cor_weight=$cor_row['value'];

//Извлекаем значение Максимального Интервала Публикаций
$per_sql="select value from rating_scalar_params where name='period'";
$per_res=mysql_query($per_sql);
while ($per_row = mysql_fetch_assoc($per_res)) $period=$per_row['value'];

//создаем массив воскресений от первой даты голосования до первого воскресенья после последней даты голосования или создания автора или статьи

//Вычисляем первую дату голосования
$first_date_sql="select since from vote order by since ASC limit 0,1";
$first_date_res = mysql_query($first_date_sql);
$first_date_row = mysql_fetch_assoc($first_date_res);
$first_date = $first_date_row['since'];

//Вычисляем последнюю дату
$last_date_sql = "select since from vote order by since DESC limit 0,1";
$last_date_res = mysql_query($last_date_sql);
$last_date_row = mysql_fetch_assoc($last_date_res);
$last_date_1 = $last_date_row['since'];
$last_date_sql = "select since from articles order by since DESC limit 0,1";
$last_date_res = mysql_query($last_date_sql);
$last_date_row = mysql_fetch_assoc($last_date_res);
$last_date_2 = $last_date_row['since'];
$last_date_sql = "select since from authors order by since DESC limit 0,1";
$last_date_res = mysql_query($last_date_sql);
$last_date_row = mysql_fetch_assoc($last_date_res);
$last_date_3 = $last_date_row['since'];
$last_date = max($last_date_1,$last_date_2,$last_date_3);

//Вычисляем первое и последнее воскресенья
$first_sunday = date('Y-m-d 00:00:01', strtotime('next sunday', strtotime($first_date)));
$last_sunday = date('Y-m-d 00:00:01', strtotime('next sunday', strtotime($last_date)));

$sundays[]='0000-00-00 00:00:00';
//создаем массив дат, которые являются воскресеньями начиная с first_sunday и до last_sunday
for ($i=strtotime($first_sunday); $i<=strtotime($last_sunday); $i += 60*60*24*7)
{
	$sundays[]=date('Y-m-d 00:00:01',$i);
}
$sundays[]='9999-12-31 00:00:00';

for ($i=0; $i<sizeof($sundays)-1; ++$i) {
	$sql="select vote.subject_id, vote.since as since, voter_id, grade, initial_rating from vote,rating where vote.subject_type=1 and rating.subject_type=1 and rating.subject_id=vote.subject_id and vote.since >= '".$sundays[$i]."' and vote.since < '".$sundays[$i+1]."' order by since ASC";
	$res = mysql_query($sql);
	$n=0;
	while ($row = mysql_fetch_assoc($res)) {
		$article_id = $row['subject_id'];
		$vote_date = $row['since'];
		$initial_rating = $row['initial_rating'];
		$r_sql = "select sum(rating*grade)/sum(rating) + $cor_weight/sqrt(count(1))*($initial_rating-sum(rating*grade)/sum(rating)) as article_rating from vote, rating where rating.subject_type=0 and rating.subject_id=vote.voter_id and vote.subject_type=1 and vote.subject_id=$article_id and vote.since <= '$vote_date'";
		
		$r_res = mysql_query($r_sql);
		while ($r_row = mysql_fetch_assoc($r_res)) {
			$article_rating = $r_row['article_rating'];
		}
		
		mysql_query("UPDATE rating set rating=".$article_rating." WHERE subject_type=1 and subject_id=".$article_id);
		
		//Обновляем начальный рейтинг всех статей, созданных после данного голосования, но перед следующим голосованием.
		
		$next_vote_sql = "select since from vote where since>'$vote_date' order by since ASC limit 0,1";
		echo $next_vote_sql.'<br>';
		$next_vote_res = mysql_query($next_vote_sql);
		$next_vote_row = mysql_fetch_assoc($next_vote_res);
		$next_vote_date = $next_vote_row['since'];
		
		$renew_res = mysql_query("select id, since from articles where since>'$vote_date' and since<'$next_vote_date'");
		while ($renew_row = mysql_fetch_assoc($renew_res)) {
			$renew_id = $renew_row['id'];
			$renew_since = $renew_row['since'];
			$avg_rating_sql = "select avg(rating) as avg_rating from rating,articles where subject_type=1 and rating.subject_id = articles.id and articles.since >= '$renew_since'  - Interval $period day and articles.since < '$renew_since'";
			echo $avg_rating_sql.'<br>';
			$avg_res = mysql_query($avg_rating_sql);
			$avg_row = mysql_fetch_assoc($avg_res);
			$avg_rating = $avg_row['avg_rating'];
			$upd_sql = "update rating set initial_rating=$avg_rating where subject_type=1 and subject_id=$renew_id";
			mysql_query($upd_sql);
			echo $upd_sql.'<br>';
			
		}
		
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
		$n++;
		
	}
}
echo "$n records processed";

?>