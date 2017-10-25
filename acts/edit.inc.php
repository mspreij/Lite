<?php
# /acts/edit.inc.php

if (! $pkey) {
    $messages[] = styledText("Can't edit: Table has no primary key.", 'red');
    return; // todo: make it so you *can* edit tables without primary key some day [2008-12-20 21:41:57]
}
$old_view = $view;
$view = 'edit';
if ($pval) {
    $sql = "SELECT * FROM $db.`$table` WHERE `".substr($pdo->quote($pkey), 1, -1)."` = :$pkey";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$pkey=>$pval])) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if ($data) {
        $messages[] = styledText("Edit record:<br>\n", '#000', 'b');
    }else{
        $messages[] = styledText("Error selecting record:". $stmt->errorInfo()[2] ."<br>\n", 'red');
        $view = $old_view;
    }
}else {
    $messages[] = styledText("Insert new record:<br>\n", '#080');
}

