<?php

ob_end_clean();

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="'. basename($table) .'.sql"');
echo $sql_export;
die();

/* -- Log --------------------------------

[2009-04-01 12:46:24] Created.

*/
?>