<?php
require('../config.php');

$filename = $db_name."-" . date("d-m-Y") . ".sql.gz";
$mime = "application/x-gzip";

header( "Content-Type: " . $mime );
header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

$cmd = "mysqldump -u $db_user --password=$db_pass $db_name | gzip --best";   

passthru( $cmd );

exit(0);
?>