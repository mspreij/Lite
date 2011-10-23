<?php

echo 
 "<form method='post'>
	<input type='hidden' name='db' value='$db'>
	<input type='hidden' name='table' value='$table'>
	<input type='hidden' name='pval' value='$pval'>
	<input type='hidden' name='act' value='save'>
	<table class='editTable'><tr><td colspan='2'>\n";
	// todo: write and call prev_next_record($pval)..
echo "</td></tr>\n";

foreach(key_unnest($table_structure) as $name => $props) {
	inputrow($name, $props);
}

$delete_link = "$me?db=$db&table=$table&act=delete&view=$view&page=$page&pval=$pval";

echo 
	"<tr><td colspan='2'>
		<input type='submit' name='some_name' value='Update' id='some_name'>
		<input type='button' name='delete' value='Delete' id='delete' onclick='if (confirm(\"Delete Record?\")) {location.href=\"$delete_link\";}'>
	</td></tr>
	</table>
	</form>\n";

#__________________________
# inputrow($name, $props) /
function inputrow($name, $props) {
	global $data;
	$type = $props['Type'];
	$fname = "form_data[$name]";
	static $label_id = 1;
	echo "<tr><td valign='top' class='td1'>$name</td><td valign='top' class='td2'>\n";
	if ((substr($type, 0, 7) == 'varchar') or (substr($type, 0, 4) == 'char')) {                 // • Varchar, Char
		$size = preg_replace('/[^0-9]+/', '', $type);
		inputfield($fname, $data[$name], "type=text size=".min($size+2, 80)." maxlength=$size");
	}elseif ($type == 'text' or $type == 'mediumtext') {                                         // • Text
		inputfield($fname, $data[$name], "type=textarea cols=80 rows=8");
	}elseif ((substr($type, 0, 7) == 'tinyint') or (substr($type, 0, 3) == 'int')                // • Int, TinyInt, SmallInt
	          or (substr($type, 0, 8) == 'smallint')) {
		inputfield($fname, $data[$name], "type=text size=5");
	}elseif (in_array($type, array('date', 'datetime', 'timestamp'))) {                          // • Date stuffs
		$label_id++;
		inputfield($fname, $data[$name], "type=text size=16");
		echo "<input type='checkbox' onclick='input_current_timestamp(this". ($type == 'date' ? ', "date"' : '') .");' id='l".$label_id."'><label for='l".$label_id."'>Now</label>";
	// }elseif ($type == 'datetime') {                                                              // • Datetime
	// 	inputfield($fname, $data[$name], "type=text size=16");
	// 	echo "<input type='checkbox' onclick='input_current_timestamp(this);'>Now";
	// }elseif ($type == 'timestamp') {                                                             // • Timestamp
	// 	inputfield($fname, $data[$name], "type=text size=16");
	// 	echo "<input type='checkbox' onclick='input_current_timestamp(this);'>Now";
	}elseif (true) {
		inputfield($fname, $data[$name], 'type=display');                                          // • Everything else (display)
	}
	echo " <span class='footnote'>$type</span></td></tr>\n";
}


// Updated [2008-07-13 15:38:24]. As always, case 'file' and similar need integrating with whatever else you're using.
// Uses htmlents, stringToAssoc, selectlist, selectlistother, input_datetime, styledText

#_________________________________________
# inputField($name, $value, $prop_input) /
function inputField($name, $value, $prop_input = array()) {
	static $label_id = 1;
	if (! $name) return $label_id++;
	if (! is_array($prop_input)) {
		$prop_input = stringToAssoc($prop_input);
	}
	$defaults = array(
		'type'        => 'text',
		'size'        => 32,
		'maxlength'   => 255,
		'rows'        => 4,
		'maxrows'     => 20,
		'cols'        => 60,
		'class'       => '',
		'wrap'        => 'virtual',
		'checked'     => '',
		'link_params' => '',
		'text'        => '',
		'extra'       => ''
		);
	$props = array_merge($defaults, $prop_input);
	foreach($props as $key => $waarde) {
		if (is_string($waarde)) $props[$key] = rawurldecode($waarde);
		// $props[$key] = str_replace('%20', ' ', $waarde);
	}
	extract($props);
	if ($type == 'none') return false;          # do not display
	if ((bool) $checked) $checked = "checked='checked'";  // 'checked'
	if (isset($ml)) $maxlength = $ml;           // 'maxlength'
	switch (strtolower($type)) {
		case 'text':                                    # text
			echo "  <input type='text' name='$name' size='$size' maxlength='$maxlength' value='".htmlents($value)."' class='$class' ". @$options .">$extra\n";
			break;   
		case 'textarea':                                # textarea
			if (! is_int($rows)) {
				if (substr($rows, 0, 1) == 'n') $rows = min(substr_count($value, "\n") + (int) substr($rows, 1), $maxrows);
			}
			echo "  <textarea rows='$rows' cols='$cols' name='$name' wrap='$wrap' class='$class' style='vertical-align: top;'>".htmlents($value)."</textarea>\n";
			break;
		case 'checkbox':                                # checkbox
			echo "  <label for='l_$label_id'><input type='checkbox' name='$name' id='l_$label_id' value='".htmlents($value)."' $checked> $label</label>\n";
			$label_id++;
			break;
		case 'checkbool':                               # checkbool
			echo "  <input type='hidden' value='0' name='$name'>\n".
					 "  <input type='checkbox' name='$name' value='1' ". ($value?'checked':'') ."> $text\n";
			break;
		case 'radio':                                   # radio
			echo "  <label for='l_$label_id'><input type='radio' name='$name' id='l_$label_id' value='".htmlents($value)."' $checked> $label</label>\n";
			$label_id++;
			break;
		case 'select':                                  # select (external)
			if (is_string($list)) {
				$list = stringToAssoc($list);
				foreach($list as $key => $item) {
					$list[$key] = rawurldecode($item);
				}
			}
			selectList($name, $list, $value, @$usekeys);
			break;
		case 'selectother':                             # selectother (external)
			if (is_string($list)) {
				$list = stringToAssoc($list);
				foreach($list as $key => $item) {
					$list[$key] = rawurldecode($item);
				}
			}
			selectlistother($name, $list, $value, @$usekeys);
			break;
		case 'datetime':                                # datetime (external)
			input_datetime($name, $value, array_merge($props, array('show_time'=>true)));
			break;
		case 'date':                                    # date (external)
			input_datetime($name, $value, array_merge($props, array('show_time'=>false)));
			break;
		case 'password':                                # password
			echo "  <input type='password' name='$name' size='$size' maxlength='$maxlength' value='".htmlents($value)."'>\n";
			break;
		case 'file':                                    # file
			echo "  <input type='file' name='$name'>\n";
			break;
		case 'image':                                   # image (calls file)
			if (! isset($preview)) $preview = $value;
			if ($preview) echo "<a href='".str_replace('%2F', '/', urlencode($path)).urlencode($value)."' target='_blank'><img src='".str_replace('%2F', '/', urlencode($path)).urlencode($preview)."'></a><br>";
			inputField($name, $value, 'type=file');
			break;
		case 'hidden':                                  # hidden
			echo "  <input type='hidden' name='$name' value='".htmlents($value)."'>\n";
			break;
		case 'display':                                 # display
			echo $value."\n";
			break;
		default:
			echo styledText("inputField() error: Unsupported type: $type<BR>", 'red');
			break;
	}
}


/* -- Log --------------------------------

[2011-10-23 23:24:03] mediumtext. there's probably a smarter way, get on that sometime.
[2009-09-16 03:10:34] Added smallint to inputField()
[2009-01-06 04:49:56] inputrow() could be shortened, using assoc array for property lists, indexed by type

todo: inputrow() needs date[time] pickers as well.

*/

?>