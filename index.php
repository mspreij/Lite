<?php
require_once './inc/lite.inc.php';

$page_arr['title'] = 'Lite!®';
$page_arr['stylesheet'][] = 'css/style.css';
$page_arr['stylesheet'][] = 'css/date_input.css';
$page_arr['js_include'][] = 'js/jquery.date_input.js';
$page_arr['js_include'][] = 'js/scripts.js';
if (isset($_POST['query'])) $page_arr['body'] = "onload=\"$('#queryInput').focus();\"";

pageStart($page_arr);

login_form();
echo 
 "Connected to $server as $username.
	Debug: <a href='". merge_link(array('debug'=>((int) ! $debug))) ."'>Toggle</a>;
	".popup_link('inc/admin_sql.php', 'Administration SQL', 910, 580, array('return'=>1))."
	<hr size='1'>\n";

# collect some incoming
foreach(explode(' ', 'db table act query view page pval') as $field) $$field = trim(@$_REQUEST[$field]);
$messages          = array();
$page              = (int) $page;
$pval              = (int) $pval;
if (! $view) $view = 'text';
$pkey              = null;
$table_structure   = array();
# Browsing larger tables, nr of row to show at once:
$show_rows         = 20;

if (! $act) $messages[] = "Welcome to Lite®!\n"; // todo: check and warn for invalid act

if ($db) {
	if (mysql_select_db($db)) {
		// good
		if ($table_statuses = fetch_rows("SHOW table status")) { // [2012-01-10 19:42:15]
			$table_statuses = array_map('lower_keys', key_unnest($table_statuses));
			if ($table) {
				if (! in_array($table, array_keys($table_statuses))) {
					trigger_error("table '$table' not in table list", E_USER_WARNING);
					unset($table);
				}
			}
		}
	}else{
		$messages[] = styledText("No valid database provided.<br>\n", 'red');
		$db = '';
	}
}
if (DEBUG) {
	foreach(explode(' ', 'db table act view page pval') as $field) echo " | $field: <strong>{$$field}</strong>";
	echo " |<hr size='1'>\n";
}
# -- End top row, let's go do stuff! -----
/*
	Here we look at $act which is handled, and can define or reset the $view.
	Some acts are special, and .. uh.. yeah.
	Most acts need at least a table and a database: 'structure', 'browse', 'new', 'save', 'delete', 'duplicate', 'insert'
*/


if ($table) {
	$pkey = get_primary_key($table);
}


# Poor man's try/catch through the acts:

do {                                                                    // •• Act is ...
	if (! db_and_table()) {
		// $messages[] = styledText("Missing table or db.<br>\n", 'red');
		break;
	}
	if ($act == 'delete') {                                               // •• Delete
		require_once './acts/delete.inc.php';
	}
	if ($act == 'structure') {                                            // •• Structure
		require_once './acts/structure.inc.php';
	}
	if ($act == 'browse') {                                               // •• Browse
		require_once './acts/browse.inc.php';
	}
	if ($act == 'save') {                                                 // •• Save
		require_once './acts/save.inc.php';
	}
	if ($act == 'edit') {                                                 // •• Edit
		require_once './acts/edit.inc.php';
	}
	if ($act == 'structure_full') {                                       // •• Structure-Full
		require_once './acts/structure_full.inc.php';
	}
	if ($act == 'export') {                                               // •• Export
		require_once './acts/export.inc.php';
	}
	if ($act == 'showcreate') {                                           // •• what the /hell/ was I thinking?
		require_once './acts/showcreate.inc.php';
	}
} while (false);


# act 'query' is a special case, it can be valid without a specified database or table
if ($act == 'query') {
		require_once './acts/query.inc.php';
}


# -- list databases and tables, load view.
echo "<div class='DbTableDiv'>";

$db_list = list_dbs();

if ($db) {
	echo "<form action='$me' method='get' style='display: inline;'>\n";
	selectList('db', $db_list, $db, 1);
	echo 
	 "<input type='submit' name='submit' value='Use'>
	</form><br>\n";
	list_tables($db, $table);
}else{
	echo "<strong>Databases:<br></strong>";
	$i = 0;
	foreach ($db_list as $db => $label) {
		echo "<a style='display: block;' href='$me?db=$db' tabindex='".++$i."'>$label</a>\n";
	}
}

echo "</div>"; // -- end database/tables

if ($table) nav_links();
showmessages();

if ($act) {
	$file = "views/$view.php";
	if (! file_exists($file)) {
		echo styledText("View file not found!: '$view'<br>\n", 'red');
	}
	@include $file;
}

?>

<form action="index.php" method="post" onsubmit="return confirmDelete();">
	<input type="hidden" name="act" value="query">
	<input type="hidden" name="db" value="<?php echo $db; ?>">
	<input type="hidden" name="table" value="<?php echo $table; ?>">
	<textarea name="query" rows="8" cols="80" id="queryInput"><?php echo htmlents($query); ?></textarea><br>
	<input type="submit" name="" value="Go">
</form>

</body>
</html>
<?php
/* -- Log --------------------------------

[2012-01-10 19:42:15] *cough* applied "SHOW table status" in favor of "SHOW tables" to also get comments (see TS). (also: which vpad, wtf?)
                      This thing needs overhauling /so badly/
[2008-12-20 21:41:57] See vpad.

*/ ?>