<?php
class Recobject {
    
    /*
    
    Todo: this exists and works for MySQL, even for SELECT queries, so use it maybe http://php.net/manual/en/pdostatement.rowcount.php
    
    */
    
    /** -- Functions -----------------------
    * 
    * __construct($table, $fields, $id=0, $clause=false)   -- Constructor: string $table, array $fields, int $id, array $clause
    * select()                                             -- Fetches row, sets $this->fields items with update_object(), returns row.
    * set_db($db)                                          -- setter for db connection. Yells and returns false when passing something other than a PDO instance.
    * set_clause($array)                                   -- Adds assoc array (field/value) to use in all queries [2008-02-07 12:14:44]
    * add_clause($array)                                   -- Adds to current clause (instead of replacing like set_clause), does overwrite items if they were specified again
    * insert($extra='')                                    -- Gets data with get_data(), inserts record, calls select() to update object, returns id or false.
    * update($extra='')                                    -- Gets data with get_data(), updates record, calls select() to update object.
    * validate($data, $type)                               -- 
    * update_object($data)                                 -- fills $this->fields array with passed assoc array (prime candidate for private method in PHP5)
    * get_data($type='')                                   -- Grabs $_POST/GET value (if isset()) for each key in $fields, returns assoc array.
    * hook($name, $function)                               -- register $function to run in $name -> method name. $function can also be the result of create_function(),
    or an anonymous function (closure) in PHP 5.3+. AND it accepts array($object or 'object_name', 'method')
    as $function, too.
    * run_hooks($hook, &$data)                             -- Called by various functions, allows "extending" them without extending the class
    * delete()                                             -- Just deletes the record. Extend class to deal with subrecords, files etc
    * get_list($options='')                                -- what it says, returns array. Also runs update_object hook on each item (as of this writing)
    * styledText($string, $color='#000')                   -- "meh"
    * reset()                                              -- returns the object to a pre-$id state
    * add_method($name, $function)                         -- add methods on-the-fly, because why not.
    * __call($function, $arguments)                        -- handles dynamically-added methods, fatals when 
    * 
    * 
    **/
    
    public $table;                    // string,       tablename
    public $fields;                   // array,        fields[name] => value
    public $id;                       
    public $clause;                   // assoc array,  field=>'value'
    public $debug            = False;  
    public $show_errors      = 1;     // 1: errors, 2: warnings, 3: notices, 0: fail silently.
    public $messages         = array();
    public $logging          = False; // log events to logbook table, disabled while porting stuff.
    public $record_created   = "Record created.";
    public $record_updated   = "Record updated.";
    public $validation_error = '';
    public $validation_text  = 'Failed to save, invalid data: ';
    public $custom_methods   = [];
    
    protected $db;                    // db connection: for this object
    
    // -- Constructor ----------------------
    function __construct($db, $table, $fields, $id=0, $clause=false) {
        if (! ($db instanceof PDO)) trigger_error('RecObject constructor error: that is not a PDO instance.', E_USER_WARNING);
        $this->db = $db;
        // set debug to constant DEBUG, if any
        if (defined('DEBUG')) $this->debug = (int) DEBUG;
        $this->table = $table;
        if (is_array($fields)) {
            foreach($fields as $field) $this->fields[$field] = '';
        }else{
            trigger_error(get_class($this).'/'.__CLASS__.' class error: $fields should be array in constructor', E_USER_WARNING);
        }
        $this->id = $id;
        $this->clause = array(); // init.
        if ($clause) $this->set_clause($clause);
        if (! ($table && $fields)) trigger_error('recobject class error: Missing table or fields in constructor', E_USER_WARNING);
        if ($this->id) {
            $this->select();
        }
    }
    
    //______________________
    // select($id = false) /
    function select($id = false) {
        if ($id) $this->id = $id;
        if (! $this->id) {
            $this->messages[] = $this->styledText("Can't select data, no id given.", 'red');
            return false;
        }
        $parameters = []; // used for binding params, below
        if (is_array($this->id)) {
            $sql = "SELECT id, `". join('`, `', array_keys($this->fields)) ."` FROM $this->table WHERE ";
            $parameters = $this->id;
        }else{
            $sql = "SELECT `". join('`, `', array_keys($this->fields)) ."` FROM $this->table WHERE ";
            $parameters['id'] = $this->id;
        }
        if ($this->clause) {
            // clause overrides parameters, if they overlap
            $parameters = array_merge($parameters, $this->clause);
        }
        // now finish the query string template. quote thing slightly weird, PDO has no nice "escape-column-names" method.
        foreach ($parameters as $key => $val) $sql .= "`".substr($this->db->quote($key), 1, -1)."` = :$key AND ";
        $sql = substr($sql, 0, -4);
        // Prepare query..
        $stmt = $this->db->prepare($sql);
        if ($this->debug) echo $this->styledText($sql."<br>\n", 'blue');
        // .. and run:
        if ($stmt->execute($parameters)) {
            $row      = $stmt->fetch(PDO::FETCH_ASSOC);
            $multiple = $row ? $stmt->fetch(PDO::FETCH_ASSOC) : false; // PDO doesn't seem to have mysql_num_rows() either
      
            if ($multiple) {
                // You specified an $id, I'm assuming you want a single record, but I found more.
                // If you want to select a subset of records based on some filter, use null or false for $id and use the $clause instead.
                throw new Exception(get_class($this) ." class error: more than one row match this \$id (use \$clause to find a subset of records if that was the objective)", 2);
                return false;
            }elseif ($row) {
                if (is_array($this->id)) $this->id = (int) array_shift($row);
                $this->update_object($row);
                return $this->fields;
            }else{
                $this->id = false; // the query worked, clearly there is No Such Record.
                $this->messages[] = $this->styledText(get_class($this) .": Record not found..", 'red');
                return false;
            }
            return $row;
        }else{
            trigger_error("Select error: ". var_export($stmt->errorInfo()[2], 1), E_USER_WARNING);
            return false;
        }
    }
    
    // set_db($db)
    public function set_db($db) {
        if ($db instanceof PDO) {
            $this->$db = $db;
            return true;
        }
        trigger_error(get_class($this).'->set_db() error: that is not an instance of PDO.', E_USER_WARNING);
        return false;
    }
    
    //_____________________
    // set_clause($array) /
    function set_clause($array) {
        if (! is_array($array)) {
            trigger_error(__CLASS__.'->set_clause() error, argument should be a (non-empty) array,', E_USER_WARNING);
            return false;
        }
        $this->clause = $array;
        return true;
    }
    
    //________________________
    // -- add_clause($array) /
    function add_clause($array) {
        if (! is_array($array)) {
            trigger_error(__CLASS__.'->add_clause() error, argument should be a (non-empty) array,', E_USER_WARNING);
            return false;
        }
        foreach ($array as $key => $value) {
            $this->clause[$key] = $value; // overwrites as needed
        }
        return true;
    }
    
    //______________________
    // insert(array $data) /
    function insert(array $data) {
        if ($data) {
            $this->run_hooks('pre_insert', $data);
            // Validate
            if (! $this->validate($data, 'insert')) {
                $this->messages[] = $this->styledText($this->validation_text . $this->validation_error, '#f80');
                return False;
            }
            if ($this->clause) $data = array_merge($data, $this->clause); // clause
            // make sure things are ready for inserts, fix & complain if not
            foreach ($data as $key => $value) {
                if (! (is_scalar($value) or is_null($value))) {
                    $data[$key] = json_encode($value);
                    trigger_error("->insert(): json_encoded value for field '$key' (was an array). It's better to handle this in a get_data() hook.", E_USER_NOTICE);
                }
            }
            $sql = "
                INSERT INTO $this->table (`". join('`, `', array_keys($data)) ."`)
            VALUES (:". join(', :', array_keys($data)) .")";
            $stmt = $this->db->prepare($sql);
            if ($this->debug) echo $this->styledText($sql."<br>\n", 'green', 'p');
            if ($res = $stmt->execute($data)) {
                if ($this->record_created) $this->messages[] = $this->record_created;
                $this->id = $this->db->lastInsertId();
                $this->select();
                $this->run_hooks('post_insert', $data);
                return $this->id;
            }else{
                trigger_error("Insert error: ". $stmt->errorInfo()[2], E_USER_WARNING);
                return false;
            }
        }else{
            $this->messages[] = $this->styledText("->insert: No data", 'red');
            return false;
        }
    }
    
    //______________________
    // update(array $data) /
    function update(array $data) {
        if ($data) {
            $this->run_hooks('pre_update', $data);
            // Validate
            if (! $this->validate($data, 'update')) {
                $this->messages[] = $this->styledText($this->validation_text . $this->validation_error, '#f80');
                return False;
            }
            if ($this->clause) $data = array_merge($data, $this->clause); // clause
            // create SQL template string
            $sql = "UPDATE $this->table SET\n";
            foreach($data as $key => $value) {
                if (! (is_scalar($value) or is_null($value))) {
                    $data[$key] = json_encode($value);
                    trigger_error("->update(): json_encoded value for field '$key' (was an array). It's better to handle this in a get_data() hook.", E_USER_NOTICE);
                }
                $sql .= "`$key` = :$key, ";
            }
            $sql = substr($sql, 0, -2) ." WHERE id = :id";
            $data['id'] = $this->id; // 'bind' the last param, to make it ALL PDO
            // prepare,
            $stmt = $this->db->prepare($sql);
            if ($this->debug) echo $this->styledText($sql."<br>\n", '#C60', 'p');
            // ..run:
            if ($stmt->execute($data)) {
                if ($this->record_updated) $this->messages[] = $this->record_updated;
                $data = $this->select();
                $this->run_hooks('post_update', $data);
                return true;
            }else{
                trigger_error("Update error: ". $stmt->errorInfo()[2], E_USER_WARNING);
                return false;
            }
        }else{
            $this->messages[] = $this->styledText("->update: No data", 'red');
            return false;
        }
    }
    
    
    //_________________________
    // validate($data, $type) /
    function validate($data, $type) {
        // $this->validation_error = '...';
        return true;
    }
    
    //_______________________ -- private
    // update_object($data) /
    private function update_object($data) {
        $this->run_hooks('update_object', $data);
        foreach($data as $key => $value) {
            $this->fields[$key] = $value;
        }
    }
    
    //_____________________
    // get_data($type='') /
    function get_data($type='') {
        $data = array();
        $_request = array_merge($_GET, $_POST); // [2010-04-01 20:35:02]
        foreach(array_keys($this->fields) as $field) {
            if (isset($_request[$field])) {
                $data[$field] = $_request[$field]; // [2009-11-21 17:42:42]
            }
        }
        $this->run_hooks('get_data', $data);
        return $data;
    }
    
    //_________________________ register custom functions, added [2008-11-15 19:05:38]
    // hook($name, $function) /
    function hook($name, $function) {
        $this->hooks[$name][] = $function;
        // that's the meat of it; now we'll just check if it was useful, and throw warnings/errors otherwise.
        // Todo: can't the next 20 or so lines be replaced by an is_callable() check?
        if (is_string($function)) {
            if (! function_exists($function)) {
                if ($this->show_errors > 1) {
                    trigger_error("Hook function '$function' is not defined (yet)!", E_USER_NOTICE);
                }
            }
        }elseif(is_array($function)) {
            if (count($function)==2) {
                if (! method_exists($function[0], $function[1])) {
                    throw new Exception("Hook method '". $function[1] ."' is not defined in Class '".(is_string($function[0]) ? $function[0] : get_class($function[0]))."'!", 2);
                }
            }else{
                trigger_error(get_class($this) ."->hook() parameter error: [object,method] array should have exactly 2 items.", E_USER_WARNING);
                return false;
            }
        }elseif(is_object($function) && strtolower(get_class($function)) == 'closure'){
            // cool?
        }else{
            trigger_error(get_class($this) ."->hook() did not expect to get a function of type '". gettype($function) ."', there. Try string or array (for methods).", E_USER_WARNING);
            return false; // that made no sense.
        }
        // Special case: this->hook() only runs once the object is initialized, and the data selected. update_object() only runs inside the select method, so any hooks for it would
        // be too late. So those, we run now.
        if ($name == 'update_object' and $this->id) { // only run if there is a record, too. [2012-07-29 14:55:29]
            $this->run_hooks($name, $this->fields); // Todo: prove this works the way it should and has no weird side effects >.<
        }
        return $this; // [2011-09-12 12:33:23] will this work?
    }
    
    //___________________________ [2009-11-22 01:20:09]
    // run_hooks($hook, &$data) /
    function run_hooks($hook, &$data) {
        if (isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $function) {
                if (is_string($function)) {
                    if (function_exists($function)) {
                        $data = $function($data);
                    }else {
                        trigger_error("Error: could not run hook function '$function': not defined.<br />", E_USER_ERROR);
                    }
                }elseif(is_array($function)) {
                    if (count($function)==2) {
                        if (method_exists($function[0], $function[1])) {
                            $data = $function[0]->$function[1]($data);
                        }else{
                            trigger_error("Hook method '". $function[1] ."' from Class '".(is_string($function[0]) ? $function[0] : get_class($function[0]))."' does not exist.", E_USER_ERROR);
                        }
                    }else{
                        trigger_error(get_class($this) ."->run_hooks() parameter error: [object,method] array should have exactly 2 items.", E_USER_NOTICE);
                    }
                }elseif(is_object($function) && strtolower(get_class($function)) == 'closure') {
                    $data = $function($data);
                }
            }
        }
    }
    
    //___________
    // delete() /
    function delete() {
        $post_delete_data = '';
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $parameters['id'] = $this->id;
        if ($this->clause) {
            foreach (array_keys($this->clause) as $key) $sql .= " AND `$key` = :$key";
            $parameters = array_merge($parameters, $this->clause);
        }
        if ($this->debug) echo $this->styledText($sql."<br>\n", 'purple');
        if (isset($this->hooks['post_delete']) && $this->hooks['post_delete']) {
            $post_delete_data = $this->fields;
        }
        $stmt = $this->db->prepare($sql);
        if ($res = $stmt->execute($parameters)) {
            $this->run_hooks('post_delete', $post_delete_data);
            $this->reset();
        }else{
            trigger_error($stmt->errorInfo()[2], E_USER_WARNING);
        }
        return $res;
    }
    
    
    //________________________
    // get_list($options='') /
    function get_list($options='') {
        if (is_array($options)) {
            extract($options);
        }
        // put together query:
        $sql = "SELECT id, "; // you get id for FREE!
        foreach(array_keys($this->fields) as $field) $sql .= "`$field`, ";
        $sql = substr($sql, 0, -2) ." FROM `$this->table`";
        if (isset($where))   $sql .= " WHERE $where";
        $params = []; // $where above is too old and used to change into PDO speak.. next major get_list() overhaul should fix.
        if ($this->clause)   {
            $sql .= (isset($where) ? ' AND ' : ' WHERE ');
            foreach (array_keys($this->clause) as $key) $sql .= "`$key` = :$key AND ";
            $sql = substr($sql, 0, -4); // strip trailing 'AND '
            $params = $this->clause;
        }
        if (isset($group_by)) $sql .= " GROUP BY $group_by";
        if (isset($order_by)) $sql .= " ORDER BY $order_by";
        if (isset($limit))    $sql .= " LIMIT $limit";
        // now execute it.
        if ($this->debug) echo $this->styledText($sql."<br>\n", 'blue');
        $stmt = $this->db->prepare($sql);
        if ($res = $stmt->execute($params)) {
            $results = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->run_hooks('update_object', $row);
                $results[] = $row;
            }
            return $results;
        }else{
            trigger_error("->get_list() error: ". $stmt->errorInfo()[2] ."<br>$sql<br>\n", E_USER_WARNING);
            return false;
        }
    }
    
    //_____________________________________
    // styledText($string, $color='#000') /
    function styledText($string, $color='#000') {
        return "<span style='color: $color;'>$string</span>"; // christ. there. happy now?
    }
    
    //__________
    // reset() /
    function reset() {
        $this->id = false;
        foreach ($this->fields as $key => $value) {
            $this->fields[$key] = null;
        }
    }
    
    
    // add_method(string $name, $function)
    function add_method($name, $function) {
        if (! is_callable($function) or empty($name)) {
            trigger_error("add_method takes a string name and a function object, that function wasn't callable()", E_USER_WARNING);
            return false;
        }
        $this->custom_methods[$name] = $function;
        return true;
    }
    
    
    // __call($function, $arguments)
    function __call($function, $arguments) {
        if (! empty($this->custom_methods[$function])) {
            $res = $arguments ? call_user_func_array($this->custom_methods[$function], $arguments) : call_user_func($this->custom_methods[$function]);
            return $res;
        }else{
            throw new Exception(get_class($this) .": non-existing method ". var_export($function, 1), 2);
        }
    }
    
    
} // End of Base Class

/* -- Log --------------------------------

[2017-10-25 22:55:42] this is neeeever gonna work
[2014-11-07 05:08:33] well, it parses.
[2014-11-06 21:10:36] rewriting it to use PDO, adding ->db, ->set_db()

*/