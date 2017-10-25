<?php
# /acts/save.inc.php

$view = 'edit';
$act  = 'edit';
$form_data = @$_POST['form_data'];
// var_export($_POST);
// die();
if (! is_array($form_data)) {
    throw new Exception('Error: incoming form data not array', 1);
}
if ($pval) {
	if (! $pkey) {
        throw new Exception("Update Error: missing Primary Key Field.<br>\n", 1);
	}
	$res = run_update_sql($table, $form_data, $pkey, $pval);
	if ($res === true) {
		$messages[] = styledText("Record updated.<br>\n", 'blue');
	}else{
		$messages[] = styledText("Update failed: $res<br>\n", 'red');
	}
}else{
	$res = run_insert_sql($table, $form_data);
	if (is_int($res)) {
		$messages[] = styledText("Record created.<br>\n", 'green');
		$pval = $res;
	}else{
		$messages[] = styledText("Insert failed: $res<br>\n", 'red');
	}
}

/* -- Log --------------------------------

[2012-03-28 02:10:31] added error numbers to messages
[2009-09-27 16:35:51] added "$pval = mysql_insert_id();" on successful insert (from Iason, [2009-09-21 16:53:23])

*/
