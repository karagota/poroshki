<?php
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

$cat = (int)$_GET['cat'];
$where = "  <> 0";
if ($cat!=0) $where ="='$cat'";

$res = mysql_query("select id,name from categories where `parent_id` $where");

$subcats = "<option value='0'>Все подкатегории</option>";
while ($row=mysql_fetch_assoc($res)) {
	$subcats.="<option value='{$row['id']}'>{$row['name']}</option>";
}
echo $subcats;
?>