<?php
# /acts/delete.inc.php

if (! $pkey) {
	throw new Exception("Can't delete: Table has no primary key.", 1);
}
$old_view = $view;
$view = 'browse';
if ($pval) {
	$sql = "DELETE FROM $db.`$table` WHERE `$pkey` = :$pkey";
    
    $stmt = $pdo->prepare($sql);
    
	if ($res = $stmt->execute([$pkey=>$pval])) {
		if (($deleted_count = $stmt->rowCount()) == 1) {
			$messages[] = styledText("Record deleted.<br>\n", '#000', 'b');
		}else{
			$messages[] = styledText("<strong>$deleted_count</strong> records deleted.<br>\n", 'red', 'b');
		}
		$act = 'browse';
	}else{
		$messages[] = styledText("Error deleting record:". $stmt->errorInfo()[2] ."<br>\n", 'red');
		$view = $old_view;
	}
}

