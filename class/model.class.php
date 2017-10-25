<?php
/**
 * 
 * Model simply wraps RecObject, and takes $table & $fields from the child object.
 * RecObject will yell if they go missing.
 * 
 * $db is accepted also in case.. you want to use a different $database. Or for "testing".
 * But, by default, it grabs/sets a default connection from new_db() (site.inc.php) and passes that.
 * If you need to extend Recobject directly, you Have to pass it in, since it must not depend on that sort of thing.
 * Am I doing this right, ##php?
 * 
**/
abstract class Model extends Recobject {
    
    private static $_db;
    protected $db_file = ''; // setting this on a child class will make new_db() try to use SQLITE
    
    function __construct($id=null, $clause=null, $db=null) {
        if (! self::$_db)   self::$_db = new_db($this->db_file);
        $use_db = is_null($db) ? self::$_db : $db;
        $this->id     = $id;
        $this->clause = $clause;
        // $fields as passed is names, but that only works if Recobject is *setting* the initial property.
        // Things using this wrapper don't want to pass fields, and don't want confusing syntax, so we empty $this->fields before passing its value on. Silly but it works.
        $fields = $this->fields;
        $this->fields = [];
        parent::__construct($use_db, $this->table, $fields, $this->id, $this->clause);
    }
    
}
