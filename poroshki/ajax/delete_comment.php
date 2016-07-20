<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

if (!$user->is_logged()) {
    die(json_encode(array('error'=>'please login')));
}

if (empty($_GET['id'])) {
    die(json_encode(array('error'=>'empty cgi-params')));
}

$error = '';
$sql = "DELETE from comments where author_id=".$user_id." and id=".$id;
mysql_query($sql);

echo json_encode(array( 'error'=>$error));
?>