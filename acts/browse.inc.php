<?php
# /acts/browse.inc.php

$total_rows_in_table = fetch_field("SELECT COUNT(*) FROM $db.`$table`"); // this query is optimized in MySQL
$skip = $show_rows * $page;
$sql = "SELECT * FROM $db.`$table` ORDER BY `". ($pkey ? $pkey: join('`, `', unnest_array($table_structure, 1)) ) ."` LIMIT $skip, $show_rows";
$data = fetch_rows($sql);
$view = 'browse';

?>