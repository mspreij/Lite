<?php

$type = gettype($data);
if ($data == null) die('No data.');

echo "Congratulations! It's a".(in_array($type[0], array('i','a','o')) ? 'n':'')." $type:<br>\n";

switch ($type) {
	case 'array':
		array_dump($data, 'pb');
		break;
	case 'string':
		echo $data;
		break;
	case 'boolean':
		var_dump($data);
		break;
	default:
		var_dump($data);
		break;
}

?>