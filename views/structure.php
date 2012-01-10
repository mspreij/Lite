<?php

echo "Table structure:<br>";

array_dump($data);

if ($comment) {
	// God this is ugly..
	echo "<div class='tableComment'>Table Comment: <span>&nbsp;".htmlents($comment)."&nbsp;</span></div>";
}

echo href('"CREATE" syntax', merge_link(array('act'=>'showcreate', 'query'=>"SHOW CREATE TABLE $table")));

?>