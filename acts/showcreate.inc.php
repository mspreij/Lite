<?php
# /acts/showcreate.inc.php

$sql  = "SHOW CREATE TABLE `$table`";
$data = fetch_row($sql);
$view = 'showcreate';

?>