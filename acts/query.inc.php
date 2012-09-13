<?php
# /acts/query.inc.php

$res = mysql_query($query); // todo: warn for DROP
$type = gettype($res);
if ($type == 'resource') {
	if (mysql_num_rows($res)) {
		while($row = mysql_fetch_assoc($res)) {
			$data[] = $row;
			$view = 'text';
		}
	}else{
		$messages[] = "Zero rows in result set.<br>";
		$data = '';
		$view = 'text';
	}
}elseif($type == 'boolean') {
	if ($res) {
		$messages[] = styledText("Result: (bool) True.<br>\nWhatever you did, worked.<br>\n", '#003F96');
	}else{
		$messages[] = styledText("Error or no result.<br>\n". mysql_error() .' ('.mysql_errno().')', 'red');
	}
}

/* -- Log --------------------------------

[2012-03-28 02:10:31] added error numbers to error message

*/