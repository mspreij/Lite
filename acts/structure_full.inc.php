<?php
# /acts/structure_full.inc.php

$data    = fetch_rows("SHOW FULL COLUMNS FROM $db.$table");
$view    = 'structure';
$comment = $table_statuses[$table]['comment']; // [2012-01-10 19:41:26] yay

?>