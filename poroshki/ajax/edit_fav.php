<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$remove = (int)$_GET['remove'];

if (!$user->is_logged()) {
    die(json_encode(array('error'=>'please login')));
}

if (empty($_GET['id'])) {
    die(json_encode(array('error'=>'empty cgi-params')));
}

$error = '';
if ($remove) 
	$sql = "DELETE FROM favorites where author_id=".$user_id." and article_id=".$id;
else 
{
	$sql = "INSERT INTO favorites (author_id, article_id) values (".$user_id.",".$id.");";
	$sql2 = "INSERT INTO `views` (subject_type, subject_id, viewer_id) values (1, ".$id.", ".$user_id.");";
	mysql_query($sql2);
}
mysql_query($sql);

$auth_sql = "select 1 from articles where id=$id and author_id=".$user->user_id;
$user_is_author = (mysql_num_rows(mysql_query($auth_sql))>0);
if ($user->voted(1,$id) || $user_is_author) {
	$sql = "select count(1) as num from favorites where article_id=".$id;
	$fav_row = mysql_fetch_assoc(mysql_query($sql));
	$fav = $fav_row['num'];

} else $fav='...';
echo json_encode(array( 'error'=>$error,
						'fav' =>$fav
						));
?>