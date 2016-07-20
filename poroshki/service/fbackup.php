<?php
require('../config.php');
$filename = 'download-' . date("d-m-Y_(G_i_s)") . ".tar.gz";
$mime = "application/x-tgz";
header("Content-Type: " . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
$cmd = 'tar -C '. $_SERVER['DOCUMENT_ROOT'].' -cz '.$included_in_backup;
passthru($cmd);
exit(0);
?>