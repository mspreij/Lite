<?php

echo "Table structure:<br>";

array_dump($data);

echo href('"CREATE" syntax', merge_link(array('act'=>'showcreate', 'query'=>"SHOW CREATE TABLE $table")));

?>