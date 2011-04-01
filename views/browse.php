<?php

if ($data) {
	foreach($data as $key => $row) foreach($row as $field => $value) if (strlen($value) > 50) $data[$key][$field] = substr($value, 0, 50) . '...';
	echo "<table class='browseTable'>\n";
	if ($total_rows_in_table > count($data)) echo "<tr><td colspan='".(count($data[0]) + 1)."'>". googlinks($show_rows, $total_rows_in_table) ."</td></tr>\n";
	echo "<tr><th>.</th><th>". join('</th><th>', array_keys($data[0])) ."</th></tr>\n";
	foreach($data as $row) {
		echo "<tr>";
		if (isset($pkey)) {
			echo "<td><a href='$me?db=$db&table=$table&act=edit&view=$view&page=$page&pval={$row[$pkey]}'>Edit</a></td>";
		}else{
			echo "<td><span style='color: gray;'>Edit</span></td>";
		}
		echo "<td nowrap>". join('</td><td nowrap>', array_map('htmlents', $row)) ."</td></tr>\n";
	}
	echo "</table>";
}else{
	echo "<br>No records.<br>";
}

?>