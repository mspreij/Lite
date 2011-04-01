<?php

$type = gettype($data);
if ($data == null) die('No data.');

echo 
 "Not sure why you're here, we should have better views.<br />
	Anyway, you brought a".(in_array($type[0], array('i','a','o')) ? 'n':'')." $type:<br>\n";

switch ($type) {
	case 'array':
		array_dump($data, 'p');
		break;
	case 'string':
		echo htmlents($data);
		break;
	case 'boolean':
		var_dump($data);
		break;
	default:
		var_dump($data);
		break;
}

?>