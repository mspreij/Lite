<?php
# /acts/save.inc.php

$view = 'edit';
$act  = 'edit';
$form_data = @$_POST['form_data'];
if (! is_array($form_data)) {
	$messages[] = styledText('Error: incoming form data not array', 'red');
	break;
}
if ($pval) {
	if (! $pkey) {
		$messages[] = styledText("Update Error: missing Primary Key Field.<br>\n", 'red');
		break;
	}
	$sql = update_sql($table, $form_data, $pkey, $pval);
	if (mysql_query($sql)) {
		$messages[] = styledText("Record updated.<br>\n", 'blue');
	}else{
		$messages[] = styledText("Update failed: ". mysql_error()."<br>\n", 'red');
	}
}else{
	$sql = insert_sql($table, $form_data);
	if (mysql_query($sql)) {
		$messages[] = styledText("Record created.<br>\n", 'green');
		$pval = mysql_insert_id();
	}else{
		$messages[] = styledText("Insert failed: ". mysql_error()."<br>\n", 'red');
	}
}

/* -- Log --------------------------------

[2009-09-27 16:35:51] added "$pval = mysql_insert_id();" on successful insert (from Iason, [2009-09-21 16:53:23])

*/

?>