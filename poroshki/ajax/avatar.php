<?php
date_default_timezone_set('Europe/Moscow');
@session_start();
$filename = urldecode($_GET['file']);
$root_path = "poroshki/";
$filepath = $_SERVER['DOCUMENT_ROOT'].$root_path.'dist/jqupload/server/php/files/medium/'.$filename;
//$filepath = $_SERVER['DOCUMENT_ROOT'].$root_path.'dist/jqupload/server/php/files/'.$filename;
/*
echo 'source='.$filepath."\r\n";
echo 'dest='.$_SERVER['DOCUMENT_ROOT'].$root_path.'images/avatars/'.$_SESSION['user_id'].'.'.strtolower(pathinfo($filename, PATHINFO_EXTENSION))."\r\n";
exit(0);*/
if (file_exists($filepath))
{
	copy ($filepath,$_SERVER['DOCUMENT_ROOT'].$root_path.'images/avatars/'.$_SESSION['user_id'].'.'.strtolower(pathinfo($filename, PATHINFO_EXTENSION)));
}
?>
