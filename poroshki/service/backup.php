<?php
require('../config.php');
$cmd = "mysqldump -h ".$db_remotehost." -u ".$db_user." -p".$db_pass." ".$db_name." -r ". $_SERVER['DOCUMENT_ROOT'].'/'.$dumpdir.'/'.$db_name."_".date('dmYhis').".dump; ";
shell_exec($cmd);
?>