<?php
# /acts/structure_full.inc.php

$data = fetch_rows("SHOW FULL COLUMNS FROM $table");
$view  = 'structure';

?>