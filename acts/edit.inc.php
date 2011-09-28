<?php
# /acts/edit.inc.php

if (! $pkey) {
	$messages[] = styledText("Can't edit: Table has no primary key.", 'red');
	return; // todo: make it so you *can* edit tables without primary key some day [2008-12-20 21:41:57]
}
$old_view = $view;
$view = 'edit';
if ($pval) {
	$sql = "SELECT * FROM `$table` WHERE `$pkey` = '". mysql_real_escape_string($pval) ."'";
	if ($data = fetch_row($sql)) {
		$messages[] = styledText("Edit record:<br>\n", '#000', 'b');
	}else{
		$messages[] = styledText("Error selecting record:". mysql_error() ."<br>\n", 'red');
		$view = $old_view;
	}
}else {
	$messages[] = styledText("Insert new record:<br>\n", '#080');
}

?>
