<?php

echo "Table structure:<br>";

array_dump($data);

echo href('"CREATE" syntax', merge_link(array('act'=>'query', 'query'=>"SHOW CREATE TABLE $table")));

?>