<?php
# /acts/showcreate.inc.php

$sql  = "SHOW CREATE TABLE $db.`$table`";
$data = fetch_row($sql);
$view = 'showcreate';

?>