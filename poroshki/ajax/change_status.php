<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

$id = (int)$_GET['id'];
$status=(int)$_GET['status'];

if (!$user->is_logged()) {
    die(json_encode(array('error'=>'please login')));
}

if (empty($_GET['id'])) {
    die(json_encode(array('error'=>'empty cgi-params')));
}

$error = '';
$sql = "UPDATE articles set status=$status where author_id=".$_SESSION['user_id']." and id=".$id;
mysql_query($sql);

if ($status==0) {
	mysql_query("delete from log_rating where subject_type=1 and subject_id=".$id);
	mysql_query("delete from rating where subject_type=1 and subject_id=".$id); 
} 

echo json_encode(array( 'error'=>$error));
?>