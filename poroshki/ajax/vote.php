<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";

include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
include_once($_SERVER['DOCUMENT_ROOT'].$root_path ."infobar.php");
$id = (int)$_GET['id'];
$vote = (int)$_GET['vote'];
$user_id = $_SESSION['user_id'];
$type = $_GET['type'];
$since = date("Y-m-d H:i:s");
if ($type=='comment') {$type=2;$table = 'comments';}
else {$type=1;$table='articles';}

//если глобально разрешено аннулировать голос, то считываем параметр из get
if ($annulate) $annulate_command = (int)$_GET['annulate'];
$error = '';
if ($annulate_command){
	$vote=-$vote;
}
if ($type==1) {
	$sql = "select id from $table where author_id=$user_id and id=$id";
	if (mysql_num_rows(mysql_query($sql))>0) {
	 die(json_encode(array('error'=>'Вы не можете голосовать за свой текст')));
	}
	if (!$user->hasRight('rate_article')) {
		die(json_encode(array('error'=>'Ваш лимит голосов равен нулю. Публикуйте больше статей.')));
	}
	$voteper_sql="select value from rating_scalar_params where name='vote_period'";
	$voteper_res=mysql_query($voteper_sql);
	while ($voteper_row = mysql_fetch_assoc($voteper_res)) $voteper=$voteper_row['value'];

	$sql = "select since from $table where id=$id and since<(Now() - Interval ".$voteper." day)";
	if (mysql_num_rows(mysql_query($sql))>0) {
	 die(json_encode(array('error'=>'Истек срок голосования за статью. Ваш голос не учтен.')));
	}
}

if (!$user->is_logged()) {
    die(json_encode(array('error'=>'please login')));
}

if (empty($_GET['id']) || empty($_GET['vote'])) {
    die(json_encode(array('error'=>'empty cgi-params')));
}


//сделать проверку на то, что это именно переголосование
//считываем из БД были ли голоса за этот порошок раньше от этого автора
$already_voted_sql = "select count(1) as num from vote where subject_id=".$id." and subject_type=1 and voter_id=".$user_id;
//echo $already_voted_sql.'<br>';
$already_voted_res = mysql_query($already_voted_sql);
$already_row = mysql_fetch_assoc($already_voted_res);
if ($already_row['num']>0)
{
	if ($type==1 && !$user->can_revote())
	die(json_encode(array('error'=>'Вы больше не можете менять свое решение в этом месяце.')));
}

if ($user->hasRight($type,$id )) {
$sql = "SELECT sum(grade)+".$vote." as new_grade from vote where voter_id=".$user_id." and subject_id=".$id." and subject_type=".$type;
//echo $sql;
$res = mysql_query($sql);
while ($row= mysql_fetch_assoc($res)) $new_grade = $row['new_grade'];
if (abs($new_grade)>1) die(json_encode(array('error'=>'Вы уже проголосовали. Повторный голос не учтён.'))); 
    $sql = "INSERT INTO `vote` (`id`, `voter_id`, `subject_id`, `subject_type`, `grade`, `since`, `last`) VALUES (NULL, '".$user_id."', '".$id."', '".$type."', '".$vote."', '".$since."', '".$since."')";
	//echo $sql.'<br>';
    mysql_query($sql);
	$sql = "INSERT INTO views (subject_type, subject_id, viewer_id) values ($type,$id,$user_id)";
	mysql_query($sql);
	
}
else $error = "not enough rights to vote";



if ($type=='1') $sql = "select * from authors where id = (select author_id from articles where id=".$id.")";
else $sql = "select * from authors where id = (select author_id from articles where id = (select article_id from comments where id=".$id.")) ";
$res = mysql_query($sql);
$author = mysql_fetch_assoc($res);
$articles_res = mysql_query("select count(*) as num from articles where author_id=".$author['id']);
$articles_row = mysql_fetch_assoc($articles_res);
$author['articles']=$articles_row['num'];
$rating_res =  mysql_query("select rating from rating where subject_type=0 and subject_id=".$author['id']);
$rating ="0"; while ($rat = mysql_fetch_assoc($rating_res)) $rating = round($rat['rating']*100);

$fav=0;
$fav_res = mysql_query("select count(1) as num from favorites where article_id=$id");
while ($fav_row = mysql_fetch_assoc($fav_res)) $fav = $fav_row['num'];

$grades = grades($type,$id);


?>

<?php

if ($type==1) {
	$vote_obj = array('vote'=>$vote,'subject_id'=>$id,'voter_id'=>$user_id,'since'=>$since);
	calculate_article_rating_for_a_new_vote($vote_obj);
}


 

echo json_encode(array( 
						'error'=>$error,
						'author_id'=>$author['id'],
						'nickname'=>$author['nickname'],
						'subject_type'=>$author['subject_type'],
						'name'=>$author['name'],
						'lastname'=>$author['lastname'],
						'articles'=>$author['articles'],
						'rating'=>$rating,
						'fav'=>$fav,
						'thumb_html'=>thumbs_html($type,$id,$annulate,$user),
						'grade_all' =>	$grades['all'],
						'article_rating'=>round($new_rating*500+500)
						));
?>