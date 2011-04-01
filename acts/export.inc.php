<?php
# /acts/export.inc.php

if ($data = fetch_row("SHOW CREATE TABLE $table")) {
  $data = $data['Create Table'];
  $sql_export = substr_replace($data, ' IF NOT EXISTS ', 12, 0).";\n\n";
  // now get the actual data
  $rows = fetch_rows("SELECT * FROM $table");
  if ($rows) {
    if (isset($_GET['csv'])) {
      $fh = tmpfile();
      foreach ($rows as $row) {
        fputcsv($fh, $row);
      }
      $sql_export = ''; // reset
      rewind($fh);
      while (!feof($fh)) {
        $sql_export .= fread($fh, 8192);
      }
      fclose($fh);
    }else{
      $keys = array_keys($rows[0]);
      $sql_export .= "INSERT INTO `$table` (`". join('`, `', $keys) ."`) VALUES\n";
      foreach ($rows as $row) {
        $sql_export .= "('". join("', '", array_map('mysql_real_escape_string', $row)) ."'),\n";
      }
      $sql_export = substr($sql_export, 0, -2).';';
    }
  }
  require_once './views/export.php';
}else{
  $messages[] = "Panic! Export failed, ".mysql_error();
}

/* -- Log --------------------------------


[2009-04-01 12:46:19] Created

*/
?>