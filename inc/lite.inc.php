<?php
/*
/inc/lite.inc.php

This file handles authentication and database connection, it can check for:
- presence of specific named cookie
- valid IP (from a range)
- user/pass to access application (basic http auth)
Then, server/username/password to connect to database are required.

*/


/** -- Functions -------------------------
 * 
 * new_db($options=null)                      -- null for defaults (using global consts), array for custom connection, string for sqlite db
 * login_form()                               
 * get_server_list()                          
 * write_server_list($server)                 
 * db_and_table()                             
 * select_dbs()                               
 * list_tables($db, $table)                   
 * nav_links()                                
 * get_primary_key($table)                    
 * insert_sql($table, $data)                  
 * update_sql($table, $data, $pkey, $pval)    
 * 
 * 
**/


ini_set('display_errors', '1');
error_reporting(-1);

ob_start();

require_once './inc/functions.inc.php';
$cookie_name = 'xizzy';

require_once './inc/auth.inc.php';

$me = basename($_SERVER['SCRIPT_FILENAME']);
$messages = array();
$server = $username = $password = '';
$server_file = 'aG9lcmVu.txt';
$server_list = get_server_list($server_file);
$connected = false;

$kookie = @$_COOKIE[$cookie_name];

if (get_magic_quotes_gpc()) {
    $kookie = stripslashes($kookie);
    $_POST  = stripslashes_array($_POST);
}

if (@$_POST['server__other']) $_POST['server'] = $_POST['server__other'];
if ($server = @$_POST['server'] and $username = @$_POST['username'] and $password = @$_POST['password']) {
    if (new_db(array('host'=>$server, 'user'=>$username, 'pass'=>$password))) {
        $connected = true;
        $kookie = befuddle(serialize(compact('server', 'username', 'password')), md5($u.$p), 1);
        setcookie($cookie_name, $kookie, 0, '/');
        if (! in_array($server, $server_list)) write_server_list($server);
    }else{
        $messages[] = styledtext("Error: could not log into $server as $username.", 'red');
    }
}
if (! $connected) {
    if ($kookie) {
        $kookie = befuddle($kookie, md5($username.$password), 0);
        if ($arr = unserialize($kookie)) {
            extract($arr);
            if (new_db(array('host'=>$server, 'user'=>$username, 'pass'=>$password))) {
                $connected = true;
            }else {
                $messages[] = styledText("Error logging into {$arr['server']} with {$arr['username']} (from cookie)", 'red');
            }
        }else{
            $messages[] = styledText("Error: bad data in session cookie, failed unserialize.", 'red');
        }
    } // else there's no cookie data
}

if (! $connected) {
    $messages[] = 'Not connected.';
    pageStart('Login');
    showMessages();
    login_form();
    die('</body></html>');
}

$debug = (int) @$_COOKIE['debug'];
if (isset($_GET['debug'])) {
    $debug = (int) $_GET['debug'];
    setcookie('debug', $debug, null, '/');
}
define('DEBUG', $debug);


/**
*  End code, start functions.
**/

function new_db($options=null) { // null for defaults (using global consts), array for custom connection, string for sqlite db
    static $db = false;
    if (! is_array($options) and $db) return $db;
    foreach(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $c) if (! defined($c)) define($c, '');
    $defaults = [
        'host'      => DB_HOST,
        'user'      => DB_USERNAME,
        'pass'      => DB_PASSWORD,
        'database'  => DB_DATABASE,
        'sticky'    => false, // if set to True in $options, it will replace any previously created connection
    ];
    $db_file = false;
    if (is_array($options)) {
        $defaults = array_merge($defaults, array_intersect_key($options, $defaults));
    }elseif(is_string($options) and strlen($options)) {
        $db_file = $options;
    }
    extract($defaults);
    try {
        if ($db_file) {
            $connection = new \PDO('sqlite:'.$db_file);
        }else{
            $connection = new \PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $pass);
        }
        if (! $db or $sticky) $db = $connection;
        return $connection;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}

#_______________
# login_form() /
function login_form() {
    global $server_list, $server, $me, $username;
    echo 
        '<form action="'.$me.'" method="post">';
    selectlistother('server', $server_list, $server, 0);
    echo 
        'User: <input type="text" name="username" value="'. htmlents($username) .'">
            Pass: <input type="password" name="password" value="">
    <input type="submit" name="submit" value="Use">
    </form>';
}

#_________________________
# get_server_list($file) /
function get_server_list() {
    global $messages, $server_file;
    $file = "./inc/$server_file";
    if ($data = trim(file_get_contents($file))) {
        return explode("\n", $data);
    }elseif (file_exists($file)) {
        $messages[] = styledText("Couldn't read server-list file, or empty file.", 'red');
    }else{
        $messages[] = styledText("Server list file not found.", 'red');
    }
    return array();
}

#_____________________________
# write_server_list($server) /
function write_server_list($server) {
    global $server_list, $server_file;
    $file = "./inc/$server_file";
    $server_list[] = $server;
    sort($server_list);
    $server_list = array_unique($server_list);
    return file_put_contents($file, join("\n", $server_list));
}

#_________________
# db_and_table() /
function db_and_table() {
    global $messages, $db, $table;
    if (! ($db && $table)) {
        if (DEBUG) $messages[] = styledText("Missing table and/or database.<br>\n", 'red');
        return false;
    }
    return true;
}

//_____________
// list_dbs() /
function list_dbs() {
    $rows = fetch_rows("SHOW DATABASES");
    $db_tmp = unnest_array($rows, true);
    foreach($db_tmp as $dbase) {
        $db_list[$dbase] = $dbase .' ('. (($tmp = fetch_rows("SHOW tables FROM `$dbase`")) ? count($tmp) : $tmp ) .')';
    }
    return $db_list;
}

#___________________________
# list_tables($db, $table) /
function list_tables($db, $table) {
    global $me, $messages;
    $table_tmp = fetch_rows("SHOW TABLES FROM $db");
    if ($table_tmp) {
        unnest_array($table_tmp);
        foreach($table_tmp as $value) {
            $table_list[$value] = $value .' ('. fetch_field("SELECT COUNT(*) FROM $db.`$value`") .')';
        }
        foreach ($table_list as $key => $value) {
            echo "<a href='$me?db=$db&table=$key&act=browse' class='browseLink'>[B]</a>";
            echo "<a href='$me?db=$db&table=$key&act=structure' class='structureLink ".($table == $key ? 'active':'')."'>&nbsp;$value</a>\n";
        }
    }else{
        echo "<em>No tables found.</em>";
    }
}

#______________
# nav_links() /
function nav_links() {
    if (! ($act = $_GET['act'])) $act = $GLOBALS['act'];
    echo "<div id='navLinks'>\n";
    foreach (array('structure', 'structure_full'=>'full structure', 'browse', 'edit'=>'insert', 'export') as $key => $val) {
        if (is_numeric($key)) $key = $val;
        echo href(ucwords($val), merge_link(array('act'=>$key)), ($key == $act ? 'class="active"':''));
    }
    # custom (more params)
    echo href('CSV', merge_link(array('act'=>'export', 'csv'=>'')), ($key == $act ? 'class="active"':''));
    echo "DB-server time: ". fetch_field("SELECT NOW()");
    echo "</div>
        <hr size='1'>\n";
}

#__________________________
# get_primary_key($table) /
function get_primary_key($table) {
    global $table_structure, $messages, $db;
    $pkey = null;
    $sql = "EXPLAIN $db.`$table`";
    $table_structure = fetch_rows($sql);
    if (DEBUG) $messages[] = 'get_primary_key(): '. styledText($sql, 'blue') ."<br>\n";
    foreach($table_structure as $tmp) if ($tmp['Key'] == 'PRI') $pkey = $tmp['Field'];
    return $pkey;
}

// run_insert_sql($table, $data) /
function run_insert_sql($table, $data) {
    global $db;
    $pdo = new_db();
    $sql =
       "INSERT INTO $db.$table (`". join('`, `', array_keys($data)) ."`)
        VALUES (:". join(', :', array_keys($data)) .")";
    $stmt = $pdo->prepare($sql);
    if ($res = $stmt->execute($data)) {
        return $pdo->lastInsertId();
    }else{
        return $stmt->errorInfo()[2];
    }
}

// run_update_sql($table, $data, $pkey, $pval) /
function run_update_sql($table, $data, $pkey, $pval) {
    global $db;
    $pdo = new_db();
    $sql = "UPDATE $db.`$table` SET ";
    foreach ($data as $fieldname => $value) {
        $sql .= "`$fieldname` = :$fieldname, ";
    }
    $sql = substr($sql, 0, -2) ." WHERE $pkey = :$pkey";
    $data[$pkey] = $pval;
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($data)) {
        return true;
    }
    return $stmt->errorInfo()[2];
}



/*

[2011-01-18 09:22:19] Fixed list_tables() which used to spit out silly error messages for empty dbs
[2011-01-18 09:19:46] Added display of db-server time in nav_links()
[2009-10-06 17:42:04] Added stripslashing case of magic_quotes (disable that shit, yo)
[2009-09-21 16:53:23] Added Insert link to nav_links(), corresponding edit in acts/save.inc.php
[2009-04-07 17:55:37] Added CSV export link to nav_links(), that needs cleaning up eh? Be nice if the Export button had a pulldown for different formats.

Todo: comment code. use longer md5 string (say, 128 chars) as key to befuddle server/user/pass
Todo: move functions that need to go elsewhere, elsewhere, et vice versa.
Todo: fix nav_links, it's messy, and doesn't do what it should. Ehrr.... rethink navigation in general.

*/

