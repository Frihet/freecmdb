<?php

  /** Minimal db abstraction. We use a static class like a namespace
   in order to have a single gglobal database connection with minimal
   namespace pollution.

   An added benefit is that it _might_ be possible to port this api to
   a different database abstraction than PDO if need be.

   Should be notet that there are definitely a few postgresisms in the
   database code as of today, it will not work cleanly without some
   modification on e.g. MySQL, though it should definitely be doable.
  */
class db
{
    static $debug=false;
	
    static $db;
    static $last_res;
    static $last_count=null;
    static $query_count=0;
    static $query_time = 0;
	
    static $error = null;

    /**
     Try to initialize the database. Returns true if the connection could be set up, false otherwise.
     */
    function init($dsn)
    {
        
        try {
            self::$db = new PDO($dsn, null, null, array(PDO::ATTR_PERSISTENT => true));
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            self::$error = $e->getMessage();
            error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     Returns the latest error message, if any.
     */
    function getError()
    {
        return self::$error;
    }        

    function in_list($arr) 
    {
        static $counter=0;
        $out1 = array();
        $out2 = array();
        foreach($arr as $it) {
            $out1[] = ':list_item_' . $counter;
            $out2[':list_item_' . $counter] = $it;
            $counter++;
        }
        return array(implode(", ",$out1), $out2);
    }
    
    /**
     Returns the id output of the last insert query.
     */
    function lastInsertId($param) 
    {
        return self::$db->lastInsertId($param);
    }
	
    /**
     Execute the specified query.
     */
    function query($q, $param=array())
    {
        self::$query_count += 1;
        $t1 = microtime(true);
        try 
            {
                $res = self::$db->prepare($q);

                $res->execute($param);
            }
        catch(PDOException $e) {
            self::$error = $e->getMessage();
			
            error($q . " " . sprint_r($param) . ": ".$e->getMessage());
        }
		         
        $t2 = microtime(true);
        db::$query_time += ($t2-$t1);
			
        if (self::$debug) {
            $msg = htmlEncode($q);
            if (count($param)) {
                $msg .= "\n".htmlEncode(sprint_r($param));
            }
            
            message($msg);
        }
                
        db::$last_res = $res;
        db::$last_count=null;
        return $res;
    }

    /**
     Fetch the output of the specified query as a list off hashes
     */
    function fetchList($q, $param=array()) 
    {
        $res = db::query($q, $param);
        $out = array();
        while($row = $res->fetch()) {
            $out[] = $row;
        }
        db::$last_count = $res->rowCount();
        $res->closeCursor();        
        return $out;
    }

    /**
     Fetch a single row of output from the specified query as a hash
     */
    function fetchRow($q, $param=array()) 
    {
        $res = db::query($q, $param);
        $row = $res->fetch();
        db::$last_count = $res->rowCount();
        $res->closeCursor();
        return $row;
    }

    /**
     Fetch a single value from the specified query
     */
    function fetchItem($q, $param=array()) 
    {
        $res = db::query($q, $param);
        $row = $res->fetch();
        db::$last_count = $res->rowCount();
        $res->closeCursor();
        return $row[0];
    }

    /**
     Count the number of results from the last query
     */
    function count()
    {
        if (db::$last_count !== null) {
            return db::$last_count;
        }
        return db::$last_res->rowCount();
    }

	function begin()
	{
		db::$db->beginTransaction();		
	}
	
	function commit()
	{
		db::$db->commit();		
	}
	
	function rollback()
	{
		db::$db->rollback();		
	}
	

}


?>