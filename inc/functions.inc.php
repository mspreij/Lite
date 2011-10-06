<?php

/** -- Functions -------------------------
 * 
 * pageStart($list)                                                
 * befuddle($data, $key, $way = 1)                                 
 * styledText($string, $color='black', $style='')                  
 * htmlents($string)                                               
 * selectlist($name, $list, $selected, $usekeys = 1, $extra='')    
 * selectlistother($name, $list, $selected, $usekeys = 1)          
 * stringToAssoc($string, $toggles = '')                           
 * showMessages($return = false)                                   
 * array_dump($array, $options='', $lines=0)                       
 * googlinks($links, $total, $return=0)                            
 * http_raw_query($formdata, $numeric_prefix=null, $key=null)      
 * merge_link($array, $discard=null)                               -- experimental [2008-12-18 19:07:39]
 * href($label, $link, $extra='')                                  
 * unnest_array(&$arr, $return=false)                              
 * key_unnest($arr, $first_only = false)                           
 * stripslashes_array($array)                                      
 *                                                                 
 * -- MySQL Functions --                                           
 * fetch_field($sql)                                               
 * fetch_row($sql)                                                 
 * fetch_rows($sql)                                                
 * mysql_fetch_all($res)                                           
 * 
 * 
**/


#____________________ May need updating?
#  pageStart($list) /
function pageStart($list) {
	global $me;
	$defaults = array(
		'stylesheet' => '',
		'style'      => '',
		'javascript' => '',
		'js_include' => '',
		'headertags' => '',
		'body'       => '',
		'charset'    => 'UTF-8',
		);
	if (! is_array($list)) $list = array('title'=>$list); // if not array, assume title string
	$list = array_merge($defaults, (array) $list); // merge default values with custom, first come first served
	extract($list);
	$tags = (bool) strpos(strtolower($javascript), '</script>');
	echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"
	\"http://www.w3.org/TR/html4/loose.dtd\">
<html>

<head>
	<title>". ($title?$title:'untitled') ."</title>
	<meta http-equiv='content-type' content='text/html; charset=$charset'>
	". $headertags ."
	<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js' type='text/javascript'></script>
	<link rel='stylesheet' type='text/css' href='css/default.css'>\n";
	if ($js_include) {
		if (! is_array($js_include)) $js_include = array($js_include);
		foreach($js_include as $js_incl) {
			echo "  <script src='$js_incl' type='text/javascript' charset='utf-8'></script>\n";
		}
	}
	if ($stylesheet) {
		if (! is_array($stylesheet)) $stylesheet = array($stylesheet);
		foreach($stylesheet as $tmp) {
			echo "\t<link rel='stylesheet' type='text/css' href='$tmp'>\n";
		}
	}
	echo ($style?"<style type='text/css'>\n". $style ."\n</style>\n":'') ."
". ($javascript ? ($tags ? $javascript : "<script language='javascript' type='text/javascript'>\n$javascript\n</script>\n") : '') ."
</head>

<body $body>\n"; // </body> -> This helps tm folding. Yeah.
}


# Scrambles a simple string using another simple string, can be used to obfuscate passwords
# NO support for returns, linebreaks, tabs, and a bunch of unusual ascii characters
#__________________________________
# befuddle($data, $key, $way = 1) /
function befuddle($data, $key, $way = 1) {
	$list = str_split("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ 1234567890!@#$%^&*()_+-=[]\\;',./{}|:\"<>?`~");
	$list_size = count($list);
	$output = '';
	$j = 0;
	$data = str_split($data);
	$key  = str_split($key);
	if ($way) {
		foreach($data as $char) {
			$x = array_search($char, $list) + array_search($key[$j], $list);
			if ($x >= $list_size) $x = $x - $list_size;
			$output .= $list[$x];
			$j++;
			if ($j == count($key)) $j = 0;
		}
	}else{
		foreach($data as $char) {
			$x = array_search($char, $list) - array_search($key[$j], $list);
			if ($x < 0) $x = $x + $list_size;
			$output .= $list[$x];
			$j++;
			if ($j == count($key)) $j = 0;
		}
	}
	return $output;
}


#_________________________________________________,
# styledText($string, $color='black', $style='') /
function styledText($string, $color='black', $style='') {
	if (func_num_args() == 1) return $string;
	$style_str = "color: $color; ";
	$style = ' '.$style;
	if (strpos($style, 'b')) $style_str .= ' font-weight: bold;';
	if (strpos($style, 'i')) $style_str .= ' font-style: italic;';
	if (strpos($style, 'u')) $style_str .= ' text-decoration: underline;';
	if (strpos($style, 'p')) $style_str .= ' white-space: pre;';
	$string = "<span style='$style_str'>$string</span>";
	return $string;
}


#____________________
# htmlents($string) /
function htmlents($string) {
	return htmlspecialchars($string, ENT_QUOTES);
}


#___________________________________________________________
# selectlist($name, $list, $selected, $usekeys, $extra='') /
function selectlist($name, $list, $selected, $usekeys = 1, $extra='') {
	echo "<select name='$name' $extra>\n";
	foreach($list as $key => $value) {
		$h_key   = htmlspecialchars($key, ENT_QUOTES);
		$h_value = htmlspecialchars($value, ENT_QUOTES);
		if ($usekeys) {
			echo "<option value='$h_key' " . (( (string) $key == (string) $selected)?'selected="selected"':'') . ">$h_value</option>\n";
		}else{
			echo "<option value='$h_value' " . (( (string) $value == (string) $selected)?'selected="selected"':'') . ">$h_value</option>\n";
		}
	}
	echo "</select>\n";
}


#_____________________________________________________
# selectlistother($name, $list, $selected, $usekeys) /
function selectlistother($name, $list, $selected, $usekeys = 1) {
	echo 
	 "<select name='$name'
		onchange=\"this.form.{$name}__other.style.display = (this.options[".count($list)."].selected)?'inline':'none'; return true;\">\n";
	foreach($list as $key => $value) {
		$h_key   = htmlspecialchars($key, ENT_QUOTES);
		$h_value = htmlspecialchars($value, ENT_QUOTES);
		if ($usekeys) {
			echo "<option value='$h_key' " . (( (string) $key == (string) $selected)?'selected="selected"':'') . ">$h_value</option>\n";
		}else{
			echo "<option value='$h_value' " . (( (string) $value == (string) $selected)?'selected="selected"':'') . ">$h_value</option>\n";
		}
	}
	echo "<option value=''>Other...</option>\n";
	echo "</select>\n";
	echo "<input type='text' name='{$name}__other' style='margin-left: 8px; display: ". ($list ? 'none' : 'inline') .";' />";
}


#________________________________________,
# stringToAssoc($string, $toggles = '') /
function stringToAssoc($string, $toggles = '') {
	$array = array();
	$urldecode = $bool = false;
	if (strstr(strtolower($toggles), 'u')) $urldecode = true;
	if (strstr(strtolower($toggles), 'b')) $bool      = true;
	$string = preg_split('/\s+/', $string);
	foreach($string as $pair) {
		$pair = ltrim($pair, '=');
		if (strpos($pair, '=') !== false) { # it's a pair, good
			$key = substr($pair, 0, strpos($pair, '='));
			$value = substr(strstr($pair, '='), 1);
		}else{ # no '=' sign, assume value
			$value = $pair;
		}
		if ($bool) { # convert strings 'true' & 'false' to boolean 
			if (strtolower($value) === 'false') $value = false;
			if (strtolower($value) === 'true')  $value = true;
		}
		if ($urldecode) $value = rawurldecode($value);
		# add the [key &] value
		if (isset($key)) {
			$array[$key] = $value;
			unset($key);
		}else{
			$array[] = $value;
		}
	}
	return $array;
}


#________________________________
# showMessages($return = false) /
function showMessages($return = false) {
	if (! isset($GLOBALS['messages'])) return false;
	$messages = $GLOBALS['messages'];
	$output   = '';
	if (is_array($messages)) {
		if (count($messages) > 0) {
			$output .= implode("\n", $messages);
		}
	}else{
		$output .= $messages;
	}
	$GLOBALS['messages'] = array(); // flush messages
	if ($return) {
		return $output;
	}else{
		echo $output;
		return true;
	}
}


#____________________________________________
# array_dump($array, $options='', $lines=0) /
function array_dump($array, $options='', $lines=0) {
	$options = strtolower(" $options");
	$pre     = strpos($options, 'p'); # preformatted
	$return  = strpos($options, 'r'); # echo or return?
	$nl2br   = strpos($options, 'b'); // \n -> html breaks
	$out     = '';
	$i       = 1;
	$first   = true;
	$out .= "<table border='1' cellspacing='0'>\n";
	foreach($array as $key => $value) {
		if ($first && is_array($value)) {
			$out .= '<tr><th>#</th><th>'. join('</th><th>', array_keys($value))."</th></tr>\n";
			$first = false;
		}
		$out .= "<tr>";
		if (is_string($value)) {
			$value = htmlspecialchars($value, ENT_QUOTES);
			if ($nl2br) $value = str_replace("\n", '<br>', $value);
			$out .= "<td valign='top'>$key</td><td". ($pre ? " style='white-space: pre;'" : '') .">$value</td>";
		}else if(is_array($value)) {
			$out .= "<td valign='top'>$key</td>";
			foreach($value as $col) {
				$col = htmlspecialchars($col, ENT_QUOTES);
				if ($nl2br) $col = str_replace("\n", '<br>', $col);
				$out .= "<td valign='top' ". ($pre ? " style='white-space: pre;'" : '') .">$col</td>";
			}
		}
		$out .= "</tr>\r";
		if ($lines && $i++ >= $lines) break;  }
	$out .= "</table>";
	if ($return) return $out;
	echo $out;
}


#_______________________________________
# googlinks($links, $total, $return=0) /
function googlinks($links, $total, $return=0) {
	global $me;
	$prev = 'vorige';
	$next = 'volgende';
	$page = (int) @$_GET['page'];
	$skip = $links * $page;
	$out = '';
	$b = "style='font-weight: bold;'";
	parse_str($_SERVER['QUERY_STRING'], $query);
	unset($query['page']);
	if ($query = http_raw_query($query)) $query .= '&';
	$pages = ceil($total/$links)-1;
	$items[] = ($page > 0) ? array("&laquo; $prev", "$me?{$query}page=".($page-1)) : "<span style='color: gray;'>&laquo; $prev</span>";
	for($i=0;$i<=$pages;$i++) {
		$items[] = array($i+1, "$me?{$query}page=$i");
	}
	$items[] = ($page < $pages) ? array("$next &raquo;", "$me?{$query}page=".($page+1)) : "<span style='color: gray;'>$next &raquo;</span>";
	if (! $return) {
		foreach($items as $val) $out .= ' '. (is_array($val) ? "<a href='{$val[1]}' ".(($val[0] == ($page+1)) ? $b : '').">{$val[0]}</a>" : $val);
		$out = substr($out, 1);
	}elseif ($return == 1) {
		$out = array();
		foreach($items as $val) $out[] = is_array($val) ? "<a href='{$val[1]}' ".(($val[0] == ($page+1)) ? $b : '').">{$val[0]}</a>" : $val;
	}
	return $out;
}


#_____________________________________________________________
# http_raw_query($formdata, $numeric_prefix=null, $key=null) /
function http_raw_query($formdata, $numeric_prefix=null, $key=null) {
	$res = array();
	foreach((array) $formdata as $k => $v) {
		$tmp_key = rawurlencode(is_int($k) ? $numeric_prefix.$k : $k);
		if ($key) $tmp_key = $key.'['.$tmp_key.']';
		$res[] = ( (is_array($v) || is_object($v)) ? http_raw_query($v, null, $tmp_key) : $tmp_key."=".rawurlencode($v) );
	}
	$separator = ini_get('arg_separator.output');
	return implode($separator, $res);
}


#_____________________ experimental
# merge_link($array) /
function merge_link($array, $discard=null) {
	$me = basename($_SERVER['SCRIPT_FILENAME']);
	parse_str($_SERVER['QUERY_STRING'], $query);
	$query = array_merge($query, $array);
	if ($discard) {
		$discard = (array) $discard;
		if (is_array($discard)) foreach($discard as $disc) unset($query[$disc]);
	}
	return $me .'?'. http_raw_query($query);
}


#_________________________________
# href($label, $link, $extra='') /
function href($label, $link, $extra='') {
	return "<a href='$link' $extra>$label</a>\n";
}


#_____________________________________
# unnest_array(&$arr, $return=false) /
function unnest_array(&$arr, $return=false) {
	if (! is_array($arr)) {
		echo styledText('Error: unnest_array needs array, got '. gettype($arr), 'red');
		return false;
	}
	foreach($arr as $row) $out[] = array_shift($row);
	if ($return) {
		return $out;
	}else{
		$arr = $out;
	}
}


#________________________________________
# key_unnest($arr, $first_only = false) /
function key_unnest($arr, $first_only = false) {
	foreach($arr as $row) {
		$key = array_shift($row);
		$out[$key] = $first_only ? array_shift($row) : $row;
	}
	return $out;
}


#_____________________________  works with nested arrays also
# stripslashes_array($array) /
function stripslashes_array($arr) {
	if (! is_array($arr)) {
		trigger_error("<div style='color: red;'>User-function stripslash_array() expects array as argument, got ". gettype($arr) .".</div>", E_USER_WARNING);
		return false;
	}
	$out = array();
	foreach($arr as $key => $val) {
		$out[$key] = (is_array($val)) ? stripslashes_array($val) : stripslashes($val);
	}
	return $out;
}


#__________________________________________________________________
# popup_link($link, $label, $width=300, $height=300, $options='') /
function popup_link($link, $label, $width=300, $height=300, $options='') {
	$output = '';
	$defaults = array(
		'toolbar'=>'no',
		'location'=>'no',
		'directories'=>'no',
		'status'=>'no',
		'menubar'=>'no',
		'scrollbars'=>'yes',
		'resizable'=>'yes',
		'style'=>'',
		'class'=>'',
		'return'=>false); # that last key makes the function return the code instead of echo it
	if (is_array($options)) {
		$options = array_merge($defaults, $options);          # have the values of options override those in defaults, but
		$defaults = array_intersect_key($options, $defaults); # ..kick out the keys not existing in defaults.
	}
	extract($defaults);
	$output = "<a href='#' style='$style' class='$class'
	onClick=\"MyWindow=window.open('$link','MyWindow','toolbar=$toolbar,location=$location,directories=$directories,status=$status,menubar=$menubar,scrollbars=$scrollbars,resizable=$resizable,width=$width,height=$height'); return false;\">$label</A>";
	if ((bool) $return) {
		return $output;
	}else{
		echo $output;
	}
}



# -- MySQL Functions ---------------------


#____________________ 'Coz I Can.
# fetch_field($sql) /
function fetch_field($sql) {
	if ($row = fetch_row($sql)) return array_shift($row);
	return $row;
}


#__________________
# fetch_row($sql) /
function fetch_row($sql) {
	$res = mysql_query($sql);
	if ($error_msg = mysql_error()) echo styledText($error_msg .'<br>', 'red');
	if (! $res) return false;                # return false for error
	if (mysql_num_rows($res) == 0) return 0; # 0 for 0 found
	return mysql_fetch_assoc($res);
}


#___________________
# fetch_rows($sql) /
function fetch_rows($sql) {
	$res = mysql_query($sql);
	if ($error_msg = mysql_error()) echo styledText($error_msg .'<br>', 'red');
	if (! $res) return false;                # return false for error
	if (mysql_num_rows($res) == 0) return 0; # 0 for 0 found
	return mysql_fetch_all($res);
}


#________________________
# mysql_fetch_all($res) /
function mysql_fetch_all($res) {
	$data = array();
	while ($row = mysql_fetch_assoc($res)) $data[] = $row;
	return $data;
}



/* -- Log --------------------------------

[2011-10-06 16:07:34] removed str_split() and file_put_contents() (Welcome to 2004!)
[2009-10-06 17:45:34] added stripslashes_array($array)
[2009-01-03 13:14:22] pageStart now comes with jQuery.
[2009-01-03 12:52:53] adding some more functions from online..
[2009-01-03 12:40:15] added key_unnest() (rewrite/merge from some other functions)

Todo: CLEAN UP. Jesus.

*/

?>