<?php

$type = gettype($data);
if ($data == null) die('No data.');

if (is_array($data)) {
	$sql = htmlents($data['Create Table']);
	echo "<div><textarea style='width: 750px; height: 350px;'>$sql</textarea><br>\n";
	if (strpos($sql, 'CHARSET=latin1')) {
		echo 
		 "<div id='latin1utf8' style='float: left; padding: 5px; background: #FFFCB7; color: #FF5F00; border: 1px solid #f80;'>
				I <em>know</em> it's none of my business, but .. consider changing that latin1 to utf8?
				<input type='checkbox' class='toggle' data-target='latin1utf8'> Bugger off
			</div>
			<div style='clear: both;'></div>\n";
	}
	echo "</div>";
}

?>