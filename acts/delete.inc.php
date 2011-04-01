<?php
# /acts/delete.inc.php

if (! $pkey) {
	$messages[] = styledText("Can't delete: Table has no primary key.", 'red');
	break; // todo: fix this someday, along with the other no-key ops
}
$old_view = $view;
$view = 'browse';
if ($pval) {
	$sql = "DELETE FROM `$table` WHERE `$pkey` = '". mysql_real_escape_string($pval) ."'";
	if ($res = mysql_query($sql)) {
		if (($deleted_count = mysql_affected_rows()) == 1) {
			$messages[] = styledText("Record deleted.<br>\n", '#000', 'b');
		}else{
			$messages[] = styledText("<strong>$deleted_count</strong> records deleted.<br>\n", 'red', 'b');
		}
		$act = 'browse';
	}else{
		$messages[] = styledText("Error deleting record:". mysql_error() ."<br>\n", 'red');
		$view = $old_view;
	}
}

?>