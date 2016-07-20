<?php
require('../config.php');

mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
mysql_set_charset('utf8');
mb_internal_encoding("utf8");
date_default_timezone_set( $server_time_zone );


//ВПОС
$cor_sql="select value from rating_scalar_params where name='article_cor_weight'";
$cor_res=mysql_query($cor_sql);
while ($cor_row = mysql_fetch_assoc($cor_res)) $cor_weight=$cor_row['value'];

//Извлекаем значение Максимального Интервала Публикаций
$per_sql="select value from rating_scalar_params where name='period'";
$per_res=mysql_query($per_sql);
while ($per_row = mysql_fetch_assoc($per_res)) $period=$per_row['value'];

//ВПОА
$cor_sql="select value from rating_scalar_params where name='author_cor_weight'";
$cor_res=mysql_query($cor_sql);
while ($cor_row = mysql_fetch_assoc($cor_res)) $a_cor_weight=$cor_row['value'];

//ДУСА
$top_sql="select value from rating_scalar_params where name='author_top'";
$top_res=mysql_query($top_sql);
while ($top_row = mysql_fetch_assoc($top_res)) $top=$top_row['value'];


$option = (int)$_GET['o'];
//Обновить log_rating - опция 1
if ($option==1) {
	//очистить log_rating
	mysql_query("DELETE FROM log_rating");
	echo 'удалено '.mysql_affected_rows().' записей<br><br>';
	mysql_query("alter table log_rating auto_increment = 1");
	//Для всех авторов добавить в log_rating их времена создания 
	mysql_query("insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select since, 2, id, 0, NULL, NULL, NULL, NULL from authors)");
	
	echo "insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select since, 2, id, 0, NULL, NULL, NULL, NULL from authors)".'<br>';
	echo 'добавлено '.mysql_affected_rows().' автора<br><br>';
	
	//Для всех статей добавить в log_rating их времена создания
	mysql_query("insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select since, 3, id, 1, author_id, NULL, NULL, NULL from articles where id>0)");
	echo "insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select since, 3, id, 1, author_id, NULL, NULL, NULL from articles)".'<br>';
	echo 'добавлено '.mysql_affected_rows().' статьи<br><br>';
	//для всех голосов добавить в log_rating их времена создания
	mysql_query("insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select v.since, 4, v.subject_id, 1, author_id, grade, voter_id, NULL from vote v, articles where v.subject_type=1 and v.subject_id = articles.id)");
	echo "insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select v.since, 4, v.subject_id, 1, author_id, grade, voter_id, NULL from vote v, articles where v.subject_type=1 and v.subject_id = articles.id)".'<br>';
	echo 'добавлено '.mysql_affected_rows().' голоса<br><br>';
	//...и пересчета
	//создаем массив воскресений от первой даты голосования до первого воскресенья после последней даты голосования или создания автора или статьи

	//Вычисляем первую дату голосования
	$first_date_sql="select event_date from log_rating order by event_date ASC limit 0,1";
	$first_date_res = mysql_query($first_date_sql);
	$first_date_row = mysql_fetch_assoc($first_date_res);
	$first_date = $first_date_row['event_date'];

	//Вычисляем последнюю дату
	$last_date_sql = "select event_date from log_rating order by event_date DESC limit 0,1";
	$last_date_res = mysql_query($last_date_sql);
	$last_date_row = mysql_fetch_assoc($last_date_res);
	$last_date= $last_date_row['event_date'];


	//Вычисляем первое и последнее воскресенья
	$first_sunday = date('Y-m-d 00:00:01', strtotime('next sunday', strtotime($first_date)));
	$last_sunday = date('Y-m-d 00:00:01', strtotime('next sunday', strtotime($last_date)));

	//создаем массив дат, которые являются воскресеньями начиная с first_sunday и до last_sunday
	for ($i=strtotime($first_sunday); $i<=strtotime($last_sunday); $i += 60*60*24*7)
	{
		$sundays[]=date('Y-m-d 00:00:01',$i);
	}
	foreach ($sundays as $sunday) {
		mysql_query("insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select '".$sunday."', 5, subject_id, 0, NULL, NULL, NULL, NULL from log_rating where event_type=2 and subject_type=0 and event_date <='".$sunday."')");
		echo "insert into log_rating (event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) (select '".$sunday."', 5, subject_id, 0, NULL, NULL, NULL, NULL from log_rating where event_type=2 and subject_type=0 and event_date <='".$sunday."')".'<br>';
		echo 'добавлено '.mysql_affected_rows().' пересчетов авторов<br><br>';
	}
	echo 'ok';
	die();
}
else if ($option==2)  {
	//Обнулить log_rating - опция 2
	//просто обнулить поле new_rating
	mysql_query("update log_rating set new_rating=NULL");
}
	$sql="select * from log_rating ORDER BY event_date ASC"; 
	//echo $sql.'<br>';
	$res = mysql_query($sql);
	$n=0;
	while ($row = mysql_fetch_assoc($res)) {
		$new_rating = 0; 
			switch($row['event_type']) {
				//new author
				case 2:
					//рейтинг нового автора равен среднему рейтингу пишущих авторов или 0.5
					
					$writing_authors_sql = "SELECT d.new_rating as rating FROM log_rating d INNER JOIN (SELECT subject_id, subject_type, max(event_date) AS ev_date FROM log_rating d
					  where event_date <  '".$row['event_date']."'  and (select max(since) from articles where articles.author_id=d.subject_id and articles.since < '".$row['event_date']."')> '".$row['event_date']."' - INTERVAL ".$period." day and subject_type=0
					  GROUP BY subject_id, subject_type
					  ) a ON a.subject_id= d.subject_id AND a.subject_type = d.subject_type AND a.ev_date = d.event_date
					 order by d.event_date ASC";

					//echo $writing_authors_sql.'<br>';
					$writing_res = mysql_query($writing_authors_sql);
					
					while ($writing_authors_row = mysql_fetch_assoc($writing_res)) {
						$new_rating += $writing_authors_row['rating'];
					}
					if (mysql_num_rows($writing_res)==0) $new_rating=0.5;
					else $new_rating = $new_rating/mysql_num_rows($writing_res);
					
					
					break;
				//new article
				case 3:
					$all_articles_sql ="SELECT d.subject_id, d.subject_type, d.event_date, d.new_rating as rating FROM log_rating d INNER JOIN (
					  SELECT subject_id, subject_type, max(event_date) AS ev_date
					  FROM log_rating d
					  where event_date <  '".$row[ 'event_date']."' 
						and subject_type=1
					  GROUP BY subject_id, subject_type
					  ) a ON a.subject_id= d.subject_id AND a.subject_type = d.subject_type AND a.ev_date = d.event_date
					 order by d.event_date ASC";
					
				
					
					
					$all_articles_res = mysql_query($all_articles_sql);
					while ($all_articles_row = mysql_fetch_assoc($all_articles_res)) {
						$new_rating += $all_articles_row['rating'];
					}
					if (mysql_num_rows($all_articles_res)==0) $new_rating=0;
					else $new_rating = $new_rating/mysql_num_rows($all_articles_res);
					

					break;
				//new vote
				case 4:
				//массив авторов, голосовавших за статью, и их рейтинги на момент  того голосования
					$voters_sql = "select event_date, voter_id, vote from log_rating
					where subject_type=1 and subject_id = ".$row['subject_id']." and event_type=4 and event_date <='".$row['event_date']."'";
					$voters_res = mysql_query($voters_sql);
					$voters = array();
					while ($v_row = mysql_fetch_assoc($voters_res)) {
						$rating_sql = "select new_rating as rating from log_rating where subject_type=0 and subject_id = ".$v_row['voter_id']." and event_date = (select max(event_date) from log_rating where subject_id=".$v_row['voter_id']." and subject_type=0 and event_date <= '".$v_row['event_date']."')";
						//if ($row['subject_id']==89)   echo $rating_sql.'<br>';
						$rating_res = mysql_query($rating_sql);
						$rating_row = mysql_fetch_assoc($rating_res);
						$v_row['rating'] = $rating_row['rating'];
						$voters[] = $v_row;
					}
					//if ($row['subject_id']==89) print_r($voters);
					//echo '<br>';

					$weighted_vote = 0;
					$weighted_vote_nom = 0;
					$weighted_vote_denom = 0;
					$num_voters = sizeof($voters);
					foreach ($voters as $voter)
					{
							$weighted_vote_nom += $voter['vote']*$voter['rating'];
							$weighted_vote_denom += $voter['rating'];
					}
					if ($weighted_vote_denom==0) echo 'Исключение! Сумма рейтингов авторов равна нулю!<br>';
					else {
							$weighted_vote = $weighted_vote_nom / $weighted_vote_denom;
					}
					$avg_rating_sql = "select new_rating from log_rating where event_type=3 and subject_type=1 and subject_id='".$row['subject_id']."'";
					//if ($row['subject_id']==89)  echo $avg_rating_sql.'<br>';
					$avg_rating_res = mysql_query($avg_rating_sql);
					while ($avg_rating_row = mysql_fetch_assoc($avg_rating_res))
						$avg_rating = $avg_rating_row['new_rating'];
					//if ($row['subject_id']==89)  echo 'avg_rating='.$avg_rating.'<br>';
					$new_rating = $weighted_vote + $cor_weight/sqrt($num_voters) * ($avg_rating - $weighted_vote);

					break;
				//new rating of author
				case 5:
					//А = кол-во статей автора
					//С = Сумма по ДУСА (РС+1)/(2*А)
					//B = начальный рейтинг автора
					//1. Находим начальный рейтинг автора
					$ini_sql = "select new_rating from log_rating where subject_type=0 and subject_id=".$row['subject_id']." and event_date < '".$row['event_date']."' and event_type=2";
					//if ($row['id']==16936) echo $ini_sql.'<br>';
					$ini_res = mysql_query($ini_sql);
					$ini_row = mysql_fetch_assoc($ini_res);
					$ini_rating = $ini_row['new_rating'];
					//2. Составляем список всех статей автора и их рейтингов на дату обновления рейтинга
					
					$all_articles_sql ="SELECT d.subject_id, d.subject_type, d.event_date, d.new_rating as rating FROM log_rating d INNER JOIN (
					  SELECT subject_id, subject_type, max(event_date) AS ev_date
					  FROM log_rating d
					  where event_date <  '".$row[ 'event_date']."' 
						and subject_type=1
						and author_id='".$row['subject_id']."'
					  GROUP BY subject_id, subject_type
					  ) a ON a.subject_id= d.subject_id AND a.subject_type = d.subject_type
					 and a.ev_date = d.event_date
					 and d.author_id='".$row['subject_id']."'
					order by rating DESC";
					//if ($row['subject_id']==4) echo $all_articles_sql.'<br>'; 
					$all_articles_res = mysql_query($all_articles_sql);
					$all_articles=array();
					while ($all_articles_row = mysql_fetch_assoc($all_articles_res)) {
						$all_articles[]=$all_articles_row;
					} 
					
					$top_size = round($top*sizeof($all_articles));
					if ($top_size<1 && sizeof($all_articles)>0) $top_size = 1;
					$new_articles = array_slice($all_articles,0,$top_size);
					//if ($row['subject_id']==4) print_r($new_articles);
					//echo '<br>';
					
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

					
					break;
				}
				
				//if ($row['subject_id']==89) 
				//if ($row['id']==16936) 
				echo 'event_type='.$row['event_type'].'; event_date='.$row['event_date'].'; subject_id='.$row['subject_id'].'; subject_type='.$row['subject_type'].'; new_rating='.$new_rating.'<br>';
				$update_sql = "update log_rating set new_rating=$new_rating where id=".$row['id'];
				//echo $update_sql .'<br><br><br>';  
				mysql_query($update_sql); 
				mysql_query('update rating set rating='.$new_rating.' where subject_type='.$row['subject_type'].' and subject_id='.$row['subject_id']);
				
			$n++;
			if ($n % 100 ==0 ) flush();
			//if ($n==300) break;
	}
	flush();
echo "$n records processed";
?>