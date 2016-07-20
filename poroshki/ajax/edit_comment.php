<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
$text = mysql_real_escape_string($_POST['text']);
$id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

if (!$user->is_logged()) {
    die(json_encode(array('error'=>'please login')));
}

if (empty($id)) {
    die(json_encode(array('error'=>'empty cgi-params')));
}

$sql = "UPDATE comments set `text` = '$text', last=NOW() where id=$id and author_id=$user_id";
//echo $sql;
mysql_query($sql);
echo json_encode(array( 'error'=>$error));
?>