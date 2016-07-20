<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

$id = (int)$_GET['id'];
if (!empty($_GET['viewer_id'])) $user_id = (int)$_GET['viewer_id'];
else $user_id = $_SESSION['user_id'];
//print_r($_SESSION);

if (!$user->is_logged()) {
    die(json_encode(array('error'=>'please login')));
}

if (empty($_GET['id'])) {
    die(json_encode(array('error'=>'empty cgi-params')));
}

$error = '';
//Узнать, смотрел ли автор этот порошок.
$viewed_sql = "select count(1) as views from views where subject_type=1 and subject_id=$id and viewer_id=$user_id" ;
$res =mysql_query($viewed_sql);
while ($row= mysql_fetch_assoc($res)) $views = $row['views'];

//Если не смотрел, то увидеть.
$viewed=0;
if ($views==0) {
	$sql = "INSERT INTO views (subject_type, subject_id, viewer_id) values (1,$id,$user_id)";
	$viewed=1;
}
//Если смотрел, то развидеть.
else {
	$sql = "DELETE FROM views where subject_type=1 and subject_id=$id and viewer_id=$user_id";
}
mysql_query($sql);
$error = mysql_error();


$auth_sql = "select count(1) as views from views where subject_type=1 and subject_id=$id";
//echo $auth_sql.'<br>';

$res =mysql_query($auth_sql);
while ($row= mysql_fetch_assoc($res)) $views = $row['views'];

$grade_plus = $grade_minus = 0;
$sql_plus = "select sum(grade) as num from vote where subject_type='".$type."' and subject_id=" . $id . " and grade>0";
$res_plus = mysql_query($sql_plus);
while ($row_plus = mysql_fetch_assoc($res_plus)) $grade_plus = $row_plus['num'];
$sql_minus = "select sum(grade) as num from vote where subject_type='".$type."' and subject_id=" . $id . " and grade<0";
$res_minus = mysql_query($sql_minus);
while ($row_minus = mysql_fetch_assoc($res_minus)) $grade_minus = $row_minus['num'];
if (empty($grade_minus)) $grade_minus=0;
if (empty($grade_plus)) $grade_plus=0;
$grade_all = $grade_plus + $grade_minus;

$sql = "select * from authors where id = (select author_id from articles where id=".$id.")";
//echo $sql.'<br>';
$res = mysql_query($sql);
$author = mysql_fetch_assoc($res);
$articles_res = mysql_query("select count(*) as num from articles where status=1 and author_id=".$author['id']);
$articles_row = mysql_fetch_assoc($articles_res);
$author['articles']=$articles_row['num'];
$rating_res =  mysql_query("select rating from rating where subject_type=0 and subject_id=".$author['id']);
$rating ="0"; while ($rat = mysql_fetch_assoc($rating_res)) $rating = round($rat['rating']*100);

$fav=0;
$fav_res = mysql_query("select count(1) as num from favorites where article_id=$id");
while ($fav_row = mysql_fetch_assoc($fav_res)) $fav = $fav_row['num'];
$error='';
echo json_encode(array( 'grade_plus'=>$grade_plus,
						'grade_minus'=>$grade_minus,
						'grade_all'=>$grade_all,
						'error'=>$error,
						'author_id'=>$author['id'],
						'nickname'=>$author['nickname'],
						'subject_type'=>$author['subject_type'],
						'name'=>$author['name'],
						'lastname'=>$author['lastname'],
						'articles'=>$author['articles'],
						'rating'=>$rating,
						'fav'=>$fav,
						'views' =>$views,
						'viewed' => $viewed
						));

?>