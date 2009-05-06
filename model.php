<?php

define('CI_ACTION_CREATE', 0);
define('CI_ACTION_REMOVE', 1);
define('CI_ACTION_CHANGE_TYPE', 2);
define('CI_ACTION_CHANGE_COLUMN', 3);
define('CI_ACTION_ADD_DEPENDENCY', 4);
define('CI_ACTION_REMOVE_DEPENDENCY', 5);

define('CI_COLUMN_TEXT', 0);
define('CI_COLUMN_TEXT_FORMATED', 1);
define('CI_COLUMN_LIST', 2);
define('CI_COLUMN_LINK_LIST', 3);
define('CI_COLUMN_IFRAME', 4);
define('CI_COLUMN_EMAIL', 5);
define('CI_COLUMN_DATE', 6);

class ciAction
{
    function getDescription($id) 
    {
        $desc=array(CI_ACTION_CREATE => 'CI created',
                    CI_ACTION_REMOVE => 'CI removed',
                    CI_ACTION_CHANGE_TYPE => 'CI type changed',
                    CI_ACTION_CHANGE_COLUMN => 'CI column value changed',
                    CI_ACTION_ADD_DEPENDENCY => 'Added new dependency',
                    CI_ACTION_REMOVE_DEPENDENCY => 'Removed dependency');
        return $desc[$id];
    }
    
    
}


class history
{
	
    function fetch($id) 
    {
        return db::fetchList('
select  ci_log.id, extract (epoch from create_time) as create_time, 
        ci_log.ci_id, action, 
        type_id_old, ci_log.column_id, 
        column_value_old, dependency_id, 
        cc1.value as dependency_name,
        cc2.value as dependant_name,
        ci_log.user_id,
        ci_user.username
from ci_log 
join ci_user
on ci_log.user_id = ci_user.id
left join ci_column cc1
on ci_log.dependency_id = cc1.ci_id and cc1.ci_column_type_id=6
left join ci_column cc2
on ci_log.ci_id = cc2.ci_id and cc2.ci_column_type_id=6
where ci_log.ci_id = :ci_id or ci_log.dependency_id = :ci_id
order by create_time desc', 
                             array(':ci_id'=>$id));
		

    }

    function fetchRemoves()
    {
        return db::fetchList('
select  ci_log.id, extract (epoch from create_time) as create_time, 
        ci_log.ci_id, action, 
        type_id_old, ci_log.column_id, 
        column_value_old, dependency_id, 
        cc.value as name,
        ci_log.user_id,
        ci_user.username
from ci_log 
join ci_user
on ci_log.user_id = ci_user.id
left join ci_column cc
on ci_log.ci_id = cc.ci_id and cc.ci_column_type_id=6
where ci_log.action = :action
order by create_time desc', 
                             array(':action'=>CI_ACTION_REMOVE));
        
    }
    
	
}


class log
{
    function add($ci_id, $action, $arg=null, $arg2=null) 
    {
        //echo "add($ci_id, $action, $arg);<br>";
        
        
        $value = array();
        $param=array();
                
        switch ($action) {
        case CI_ACTION_CREATE:
        case CI_ACTION_REMOVE:
            $query = "
insert into ci_log 
(
        create_time, ci_id, action, user_id
) 
values 
(
        now(), :ci_id, :action, :user_id
)";
            $param = array(':ci_id'=>$ci_id, ':action'=>$action, ':user_id'=>ciUser::$me->id);
            db::query($query, $param);
            break;
            
        case CI_ACTION_CHANGE_TYPE:
            $query = "
insert into ci_log 
(
        create_time, ci_id, action, type_id_old, user_id
) 
select now(), :ci_id, :action, ci_type_id, :user_id
from ci
where id = :ci_id";
            $param = array(':ci_id'=>$ci_id, ':action'=>$action, ':user_id'=>ciUser::$me->id);
            db::query($query, $param);
            break;
            
        case CI_ACTION_CHANGE_COLUMN:
            $query = "
insert into ci_log 
(
        create_time, ci_id, action, column_id, column_value_old, user_id
) 
select now(), :ci_id, :action, :column_id, value, :user_id
from ci_column_view
where id = :ci_id and column_type_id = :column_id";

            $param = array(':ci_id'=>$ci_id, ':action'=>$action, ':column_id'=>$arg, ':user_id'=>ciUser::$me->id);
            db::query($query, $param);
            
            break;

        case CI_ACTION_ADD_DEPENDENCY:
        case CI_ACTION_REMOVE_DEPENDENCY:
            $query = "
insert into ci_log 
(
        create_time, ci_id, action, dependency_id, user_id, dependency_type_id
)
values
(
        now(), :ci_id, :action, :dependency_id, :user_id, :dependency_type_id
)";
            
            $param = array(':ci_id'=>$ci_id, 
			   ':action'=>$action, 
			   ':dependency_id'=>$arg, 
			   ':dependency_type_id'=>$arg2, 
			   ':user_id'=>ciUser::$me->id);
            db::query($query, $param);
            break;
        }
    }

    /**
     Return a list containing the ids of the last ten CIs to be edited
    */
    function getLatestIds()
    {
        return db::fetchList("
select ci_log.ci_id 
from ci_log 
join ci 
on ci_log.ci_id = ci.id 
where ci.deleted=false 
group by ci_id 
order by max(create_time) desc 
limit 10");
    }
        
}


class dbItem
{

    /**
     * Returns an array of all public properties of this object
     * type. By convention, this is exactly the same as the list of
     * fields in the database, and also the same thing as all fields
     * whose name does not begin with an underscore.
     */
    function getPublicProperties() {
        static $cache = null;
        if (is_null( $cache )) {
            $cache = array();
            foreach (get_class_vars( get_class( $this ) ) as $key=>$val) {
                if (substr( $key, 0, 1 ) != '_') {
                    $cache[] = $key;
                }
            }
        }
        return $cache;
    }

    function initFromArray($arr)
    {
        $count = 0;
        if ($arr) {
            foreach ($this->getPublicProperties() as $key) {
                if (array_key_exists($key, $arr)) {
                    $this->$key = $arr[$key];
                    $count ++;
                }
            }
        }
        
        return $count;
        
    }
    
    function find($col_name, $col_value, $class_name, $table_name) 
    {
        $res = new $class_name();
        $data = db::fetchRow("select * from $table_name where $col_name=:value",
                             array(':value'=>$col_value));
                
        if (!$data) {
            return null;
        }
        $res->initFromArray($data);
        
        return $res;
    }
        
}

class ciType
{
    static $types=null;
    static $shapes=null;
    static $ids=null;
    
    function getTypes() 
    {
        ciType::load();
        return ciType::$types;
    }
	
    function getName($id) 
    {
        ciType::load();
        return ciType::$types[$id];
    }

    function create($name, $shape) 
    {
        db::query("insert into ci_type (name, shape) values (:name, :shape)",
                  array(':name'=>$name, ':shape'=>$shape));
        return db::count()?db::lastInsertId("ci_type_id_seq"):false;
    }
        
    function update($id, $name, $shape, $deleted) 
    {
        $val = array();
        $param = array(":id"=>$id);
        foreach(array("name", "shape", "deleted") as $key) {
            if ($$key !== null) {
                $val[] = "$key = :$key";
                $param[":$key"]=$$key;
            }
        }
            
        db::query("update ci_type set " . implode(", ", $val) . " where id=:id",
                  $param);
        return !!db::count();
    }
	
    

	
    function getShape($id) 
    {
        ciType::load();
        return ciType::$shapes[$id];
    }
    
    function getId($name) 
    {
        ciType::load();
        return ciType::$ids[$name];
    }

    function getShapes()
    {
        return array('box'=>'Box', 
                     'diamond' => 'Diamond',
                     'doubleoctagon'=>'Double octagon',
                     'ellipse'=>'Ellipse', 
                     'house'=>'House',
                     'octagon'=>'Octagon',
                     'triangle' => 'Triangle');
    }
    
    function load()
    {
        if (ciType::$types != null) {
            return;
        }

        ciType::$types=array();
        ciType::$ids=array();
        ciType::$shapes=array();
                
        foreach(db::fetchList("select * from ci_type where deleted=false order by name") as $row) {
            ciType::$types[$row['id']] = $row['name'];
            ciType::$ids[$row['name']] = $row['id'];
            ciType::$shapes[$row['id']] = $row['shape'];
        }
    }


}


class ciColumnList
{
    static $items = null;
    static $name_lookup = null;

    function getItems($column_id) 
    {
        ciColumnList::load();
        if (!array_key_exists($column_id, ciColumnList::$items)) {
            return array();
        }
        return ciColumnList::$items[$column_id];
    }
    
    function getName($id) 
    {
        ciColumnList::load();
        return ciColumnList::$name_lookup[$id];
    }
    
    function load() 
    {
        if (is_array(self::$items)) {
            return;
        }
        
        self::$items = array();
	
        foreach(db::fetchList("select * from ci_column_list order by name") as $row) {
            if(!$row['deleted']) {
                ciColumnList::$items[$row['ci_column_type_id']][$row['id']] = $row['name'];
            }
            ciColumnList::$name_lookup[$row['id']] = $row['name'];
        }
    }

    function addItem($column_id, $value){
        $query = "
insert into ci_column_list 
(
		ci_column_type_id, 
		name
) 
values 
(
		:column_id, 
		:value
)";
        $param = array(':column_id'=>$column_id, ':value'=>$value);        
        db::query($query, $param);
    }
        
    function updateItem($id, $value){
        $query = "
update ci_column_list 
set name=:value 
where id=:id";
        $param = array(':id'=>$id, ':value'=>$value);        
        db::query($query, $param);
    }

    function removeItem($id, $column_id){
        $query = "
update ci_column_list 
set deleted=true 
where id=:id 
and ci_column_type_id=:column_id";
        $param = array(':column_id'=>$column_id, ':id'=>$id);        
        db::query($query, $param);
    }
        
}


class ciColumnType
{
    static $id_lookup=null;
    static $name_lookup=null;
    static $type_lookup=null;
    static $ci_type_lookup=null;

    function getId($name) 
    {
        ciColumnType::load();
        return ciColumnType::$id_lookup[$name];
        
    }

    function create($name, $type, $ci_type) 
    {
        db::query("insert into ci_column_type (name, type, ci_type_id) values (:name, :type, :ci_type)",
                  array(':name'=>$name, ':type'=>$type, ':ci_type'=>$ci_type));
        return db::count()?db::lastInsertId("ci_column_type_id_seq"):false;
    }
        
    function update($id, $name, $type, $ci_type_id, $deleted) 
    {
        $val = array();
        $param = array(":id"=>$id);
	if($ci_type_id == "") 
	{
	    $ci_type_id = null;
	}
		
        foreach(array("name", "type", "ci_type_id", "deleted") as $key) 
	{
	    if($$key != null || $key == "ci_type_id") 
	    {
		$val[] = "$key = :$key";
		$param[":$key"]=$$key;
	    }
	}
	
        db::query("update ci_column_type set " . implode(", ", $val) . " where id=:id",
                  $param);
        return !!db::count();
    }
	
    
    function getName($id)
    {
        ciColumnType::load();
        return ciColumnType::$name_lookup[$id];
    }

    function getType($id)
    {
        ciColumnType::load();
        return ciColumnType::$type_lookup[$id];
    }

    function getCiType($id)
    {
        ciColumnType::load();
        return ciColumnType::$ci_type_lookup[$id];
    }

    function getColumns($include_none = false)
    {
        ciColumnType::load();
        if ( $include_none) 
            {
                return array(-1 => 'Any') +ciColumnType::$name_lookup;
            }
		
        return ciColumnType::$name_lookup;
    }
    
    function getTypes()
    {
        return array(CI_COLUMN_TEXT=>'Unformated text',
                     CI_COLUMN_TEXT_FORMATED=>'Multiline text with formating',
                     CI_COLUMN_LIST=>'List',
                     CI_COLUMN_EMAIL=>'Email address',
                     CI_COLUMN_DATE=>'Date picker',
                     CI_COLUMN_IFRAME=>'IFrame'/*
                                                CI_COLUMN_LINK_LIST=>'List of links'*/);
    }
    
    function load()
    {
        if (ciColumnType::$id_lookup != null) {
            return;
        }
        
        foreach(db::fetchList("select * from ci_column_type where deleted=false order by name") as $row) {
            ciColumnType::$id_lookup[$row['name']] = $row['id'];
            ciColumnType::$name_lookup[$row['id']] = $row['name'];
            ciColumnType::$type_lookup[$row['id']] = $row['type'];
            ciColumnType::$ci_type_lookup[$row['id']] = $row['ci_type_id'];
        }
        
    }

}

class ciDependencyType
{
    static $id_lookup=null;
    static $name_lookup=null;
    static $reverse_name_lookup=null;
    static $color_lookup=null;

    public $id;
    public $name;
    public $reverse_name;
    public $color;

    function __construct($id, $name=null, $reverse_name=null, $color = null) 
    {
	if( $name == null) 
	{
	    $name = self::getName($id);
	    $reverse_name = self::getReverseName($id);
	    $color = self::getColor($id);
	}
	
	$this->id = $id;
	$this->name = $name;
	$this->reverse_name = $reverse_name;
	$this->color = $color;
    }

    function isDirected()
    {
	return !!strlen($this->reverse_name);
	
    }
    
    
    function getId($name) 
    {
        ciDependencyType::load();
        return ciDependencyType::$id_lookup[$name];
    }

    function create($name, $reverse_name, $color) 
    {
        db::query("insert into ci_dependency_type (name, reverse_name, color) values (:name, :reverse_name, :color)",
                  array(':name'=>$name, ':reverse_name'=>$reverse_name, ':color' => $color));
        return db::count()?db::lastInsertId("ci_dependency_type_id_seq"):false;
    }
        
    function update($id, $name, $reverse_name, $color, $deleted) 
    {
        $val = array();
        $param = array(":id"=>$id);
        foreach(array("name", "reverse_name", "color", "deleted") as $key) 
            {
                if ($$key !== null) 
                    {
                        $val[] = "$key = :$key";
                        $param[":$key"]=$$key;
                    }
            }
		
        db::query("update ci_dependency_type set " . implode(", ", $val) . " where id=:id",
                  $param);
        return !!db::count();
    }
    
    
    function getName($id)
    {
        ciDependencyType::load();
        return ciDependencyType::$name_lookup[$id];
    }

    function getColor($id)
    {
        ciDependencyType::load();
        return ciDependencyType::$color_lookup[$id];
    }

    function getReverseName($id)
    {
        ciDependencyType::load();
        return ciDependencyType::$reverse_name_lookup[$id];
    }

    function getDependencies()
    {
	$res = array();
	
        ciDependencyType::load();
	
        foreach( ciDependencyType::$name_lookup as $id => $name) 
	{
	    $res[] = new ciDependencyType($id);
	}
	
	return $res;
    }

    function getColors()
    {
	return array("invisible"=>"Do not show in graph",
		     "black"=>"Black",
		     "blue"=>"Blue",
		     'brown'=>'Brown',
		     'cyan'=>'Cyan',
		     "green"=>"Green", 
		     "red"=>"Red",
		     'yellow'=>'yellow'
	    );
    }
    
        
    function getDependencyOptions()
    {
        ciDependencyType::load();
        $res = array();
	foreach(self::$name_lookup as $id => $name) 
	{
	    $res["$id:0"] = $name;
	    $rn = self::$reverse_name_lookup[$id];
	    if( $rn )
		$res["$id:1"] = $rn;
	    
	}
	
	return $res;
    }
        
    function getDependencyReverseNames($include_none = false)
    {
        ciDependencyType::load();
        if ( $include_none) {
	    return array(-1 => 'Any') +ciDependencyType::$reverse_name_lookup;
	}
	
        return ciDependencyType::$reverse_name_lookup;
    }
        
    function load()
    {
        if (ciDependencyType::$id_lookup != null) {
            return;
        }
        
        foreach(db::fetchList("select * from ci_dependency_type where deleted=false order by name") as $row) {
            ciDependencyType::$id_lookup[$row['name']] = $row['id'];
            ciDependencyType::$id_lookup[$row['reverse_name']] = $row['id'];
            ciDependencyType::$name_lookup[$row['id']] = $row['name'];
            ciDependencyType::$reverse_name_lookup[$row['id']] = $row['reverse_name'];
            ciDependencyType::$color_lookup[$row['id']] = $row['color'];
	}
    }

}

function ciCompare($a, $b) 
{
    return strcasecmp($a->getDescription(), $b->getDescription());
}

class ci
extends dbItem
{
    static $_table = "ci_view";
    static $_dependency_list = null;
    static $_dependency_list2 = null;
    static $_revisions=null;
    
    var $type_name;
    var $id;
    var $ci_type_id;
    var $_ci_column=null;
    var $_dependants;
    var $_dependencies;
    var $_direct_dependant;
    var $_direct_dependency;
    var $update_time;
    var $deleted;
    

    static $_cache = array();

    function setType($type)
    {
        $this->type=$type;
        db::begin();
        log::add($id, CI_ACTION_CHANGE_TYPE);
        db::query('update ci set ci_type_id=:type_id where id=:id',
                  array(':type_id'=>$type,':id'=>$this->id));
        db::commit();
        return !!db::count();
    }

    function deleteValue($key) 
    {
        db::begin();
        log::add($this->id, CI_ACTION_CHANGE_COLUMN, $key);
        $query = "
delete from ci_column
where ci_id=:id
and ci_column_type_id=:key
";
        $arr = array(':key'=>$key, ':id'=>$this->id);
        $res = db::query($query, $arr);
        db::commit();
        return !!db::count();
    }
	

    function delete()
    {
        db::begin();
        log::add($this->id, CI_ACTION_REMOVE);
        $res = db::query("update ci set deleted=true where id = :id", array('id'=>$this->id));
        db::commit();
        return !!db::count();
    }
                
    function set($key, $value) 
    {
        db::begin();
        log::add($this->id, CI_ACTION_CHANGE_COLUMN, $key);
		
        $query = "
update ci_column
set value=:value
where ci_id=:id
and ci_column_type_id=:key
";
        $arr = array(':key'=>$key, ':value'=>$value, ':id'=>$this->id);
        $res = db::query($query, $arr);
        $count = db::count();
        if (!$count) {
            $query = "
insert into ci_column
(
        ci_id,
        ci_column_type_id,
        value
)
values
(
        :id,
        :key,
        :value
)";
            $res = db::query($query, $arr);
            
        }
        db::commit();
        return !!db::count();
        
    }



    function count()
    {
        $res = db::fetchList("select count(*) cnt from $table");
        $row = $res->fetch();
        return $row['cnt'];
    }

    function apply($edit) 
    {
        if ($edit['ci_id'] != $this->id) {
            return;
        }
            
        //        echo "Apply revision ".$edit['id']." to item " .$this->id . "<br>";
            
        unset(ci::$_cache[$this->id]);
            
        if($edit['action'] == CI_ACTION_CHANGE_COLUMN) {
            $this->_ci_column[$edit['column_id']] = $edit['column_value_old'];
            //echo "change col to ".$edit['column_value_old']."<br>";
        }
        else if($edit['action'] == CI_ACTION_CHANGE_TYPE) {
            $this->ci_type_id = $edit['type_id_old'];
            //echo "change type to {$edit['type_id_old']}<br>";
        }
    }
    
    function get($name) 
    {
        return $this->_ci_column[ciColumnType::getId($name)];
    }
    
    function getDescription($long=true) 
    {
        $default_column = Property::get("ciColumn.default");
	
        $nam = $this->get(ciColumnType::getName($default_column));
        return ($nam?$nam:'<unnamed>') . ($long?(' <' . $this->type_name. ">"):'');
    }
    

    function removeDependency($other_id) 
    {
	db::begin();
	$delete_arr = array(":my_id" => $this->id, ":other_id" => $other_id);
	$type = db::fetchItem("select dependency_type_id from ci_dependency where dependency_id = :other_id and ci_id = :my_id", $delete_arr);
	
	$delete_query = "
delete from ci_dependency
where dependency_id = :other_id
and ci_id = :my_id";
        
        $res = db::query($delete_query, $delete_arr);
        
        if ($res && $res->rowCount()) {
            log::add($this->id, CI_ACTION_REMOVE_DEPENDENCY, $other_id, $type);
        }

	db::commit();
	
    }
    
    function addDependency($other_id,$type_id) 
    {
        $arr = array(':my_id' => $this->id, 
		     ':other_id' => $other_id,
		     ':type_id'=>$type_id);
        
        $res = db::query("
insert into ci_dependency 
(ci_id, dependency_id, dependency_type_id) 
values (:my_id, :other_id, :type_id)
", $arr);
        if ($res && $res->rowCount()) {
            log::add($this->id, CI_ACTION_ADD_DEPENDENCY, $other_id, $type_id);
        }
    }
    
    function getDependencies($type=null) 
    {
        if($this->_dependencies === null) 
	{
	    $this->_dependencies = ci::_getDependencies(array($this->id), true);
	}
	$res = $this->_dependencies;
	if ($type != null )
	{
	    $res = array();
	}
	
        return $res;
    }

    function isDirectDependency($id) 
    {
        $this->getDirectDependencies();
        return array_key_exists($id, $this->_direct_dependencies);
    }
    
    function isDependency($id) 
    {
        $this->getDependencies();
        
        return array_key_exists($id, $this->_dependencies);
    }
    
    
    function getDependants() 
    {
        if ($this->_dependants === null) 
            {
                $this->_dependants = ci::_getDependants(array($this->id), true);
            }
        return $this->_dependants;
    }

    function _loadDependencies()
    {
        ci::_loadRevisions();

    }
	

    function _getDependencies($id_arr, $all=false) 
    {
        ci::_loadDependencies();
			
        $dep_arr = array();
        $id_arr_map = array();
            
        foreach($id_arr as $id) {
            $id_arr_map[$id] = true;
        }
			
        if( !$all) {
	    foreach(ci::$_dependency_list as $dep) {
		if(array_key_exists($dep['ci_id'], $id_arr_map)) {
		    $dep_arr[] = $dep['dependency_id'];
		}
	    }
	} else {
	    $done = array();
	    $prev = $id_arr_map;
	    
	    while (true) {					
		$stop = true;
		foreach(ci::$_dependency_list as $dep) {
		    
		    if(array_key_exists($dep['ci_id'], $prev) &&
		       !array_key_exists($dep['dependency_id'], $done)) {
			$done[$dep['dependency_id']] = true;
			$dep_arr[] = $dep['dependency_id'];
			$next[$dep['dependency_id']] = true;
			$stop = false;
		    }
		}
		if ($stop) {
		    break;
		}
		$prev = $next;
	    }
	}
	
        $res =  ci::fetch(array('id_arr' => $dep_arr));
	
	return $res;
	
    }

    function _getDependants($id_arr, $all=false) 
    {
        ci::_loadDependencies();
			
        $dep_arr = array();
        $id_arr_map = array();
	
        foreach($id_arr as $id) {
	    $id_arr_map[$id] = true;
	}
	
        if( !$all) {
	    
	    foreach(ci::$_dependency_list as $dep) {
		if(array_key_exists($dep['dependency_id'], $id_arr_map)) {
		    $dep_arr[] = $dep['ci_id'];
		}
	    }
	} else {
	    $done = array();
	    $prev = $id_arr_map;
	    
	    while (true) {					
		$stop = true;
		foreach(ci::$_dependency_list as $dep) {
		    
		    if(array_key_exists($dep['dependency_id'], $prev) &&
		       !array_key_exists($dep['ci_id'], $done)) {
			$done[$dep['ci_id']] = true;
			$dep_arr[] = $dep['ci_id'];
			$next[$dep['ci_id']] = true;
			$stop = false;
		    }
		}
		if ($stop) {
		    break;
		}
		$prev = $next;
	    }
	}

        return ci::fetch(array('id_arr' => $dep_arr));
	
    }
	
    
    function getDirectDependencies() 
    {
        if ($this->_direct_dependencies === null) {
            $this->_direct_dependencies = ci::_getDependencies(array($this->id));
        }
        return $this->_direct_dependencies;
    }
    
    function getDirectDependants() 
    {
        if ($this->_direct_dependants === null) {
            $this->_direct_dependants = ci::_getDependants(array($this->id));
        }
        return $this->_direct_dependants;
    }
	
    function isDirectDependant($id) 
    {
        $this->getDirectDependants();
        return array_key_exists($id, $this->_direct_dependants);
    }
    
    function isDependant($id) 
    {
        $this->getDependants();
        return array_key_exists($id, $this->_dependants);
    }

    function applyAll()
    {
        ci::_loadRevisions();
        if(array_key_exists($this->id, ci::$_revisions)) {
            foreach(ci::$_revisions[$this->id] as $edit) {
                $this->apply($edit);
            }
        }      
    }
    
    function _loadRevisions()
    {
        if (ci::$_revisions!==null) {
            return;
        }
        
        $revision_id = param('revision_id');
        if ($revision_id === null) {
            ci::$_revisions = array();
            $rev=array();
        }
        else {
            
            $rev = db::fetchList('
select cl2.id, extract (epoch from cl2.create_time) as create_time, cl2.ci_id, cl2.action, cl2.type_id_old, cl2.column_id, cl2.column_value_old, cl2.dependency_id, cl2.dependency_type_id
from ci_log as cl
join ci_log as cl2
on cl2.id > cl.id
where cl.id=:revision_id
order by create_time desc', 
                                 array(':revision_id'=>$revision_id));
            ci::$_revisions=array();
            foreach($rev as $revision) {
                ci::$_revisions[$revision['ci_id']][] = $revision;
            }
        }
        

        $remove=array();
        $add = array();
        
        foreach($rev as $edit) {
            switch ($edit['action']) {
            case CI_ACTION_ADD_DEPENDENCY:
                $remove[$edit['ci_id']][$edit['dependency_id']]=true;
                $add[$edit['ci_id']][$edit['dependency_id']]=false;
                break;
                
            case CI_ACTION_REMOVE_DEPENDENCY:
                $remove[$edit['ci_id']][$edit['dependency_id']]=false;
                $add[$edit['ci_id']][$edit['dependency_id']]=$edit;
                break;
            }
        }

        $dep_list = array();
        $query = "
select ci_id, dependency_id, dependency_type_id
from ci_dependency 
";
        
        foreach(db::fetchList($query) as $dep) {
            $id = $dep['ci_id'];
            $dep_id = $dep['dependency_id'];
            
            if (array_key_exists($id, $remove) && 
		array_key_exists($dep_id, $remove[$id]) && 
		$remove[$id][$dep_id]) {
                continue;
            }
            $dep_list[] = $dep;
	    ci::$_dependency_list2[$id][$dep_id] = $dep['dependency_type_id'];
        }

        foreach($add as $ci_id => $add_list) {
            foreach($add_list as $dep_id => $edit) {
                if ($edit != false) {
                    $dep_list[] = array('ci_id'=>$ci_id, 'dependency_id'=>$dep_id, 'dependency_type_id'=>$edit['dependency_type_id']);
		    //message("restoring dependency from $ci_id to $dep_id with type ".$edit['dependency_type_id']);
		    ci::$_dependency_list2[$ci_id][$dep_id] = $edit['dependency_type_id'];
		    
                }
            }
        }
//	message(sprint_r(ci::$_dependency_list2));

        ci::$_dependency_list = $dep_list;
    }
    
    function getDependencyType($dep) 
    {
	//echo "id: " . $this->id. ", dep id: " . $dep->id . " ";
/*	echo "<pre>";
	ciDependencyType::getColor(2);
	
	echo sprint_r(ciDependencyType::$color_lookup);
	echo "</pre>";
	$f = new CiDependencyType(6);
	echo $f->color;
	exit(0);
*/	

	
	self::_loadRevisions();
	$id = self::$_dependency_list2[$this->id][$dep->id];
	if($id == null) 
	{
	    return null;
	}
	return new CiDependencyType($id);
    }

    function fetch($param=array()) 
    {
        if (array_key_exists('count', $param)) {
            return ci::fetchUncached($param);
        }
        
        if (array_key_exists('id_arr', $param)) {
				
            $id_arr = $param['id_arr'];
            $id_arr2=array();
            $res=array();
            if (!$id_arr) {
                return array();
            }
                        
            foreach($id_arr as $id) 
                {
                    if (array_key_exists($id, ci::$_cache)) 
                        {
                            $res[$id] = ci::$_cache[$id];
                        }
                    else 
                        {
                            $id_arr2[] = $id;
                        }
                }
            if (count($id_arr2)) 
                {
                    $param['id_arr'] = $id_arr2;
                    $res2 = ci::fetchUncached($param);
                    $res = $res + $res2;
                }
            return ci::sortFetchResult($res);
        }
        else {
            return ci::sortFetchResult(ci::fetchUncached($param));
        }
    }


    function sortFetchResult($arr) 
    {
			
        uasort($arr,"ciCompare");
        return $arr;
    }
	
	
    function fetchUncached($param=array()) 
    {
        $where = array();
        $db_param = array();
        $limit = "";
        $offset = "";
        $join = "";
			
        
        if (array_key_exists('id_arr', $param)) {
            if (count($param['id_arr'])==0) 
                {
                    return array();
							
                }
					
            list($id_arr_param, $id_arr_named) = db::in_list($param['id_arr']);
            $where[] = "id in ($id_arr_param)";
            $db_param = array_merge($db_param, $id_arr_named);
        }

	if(!array_key_exists('deleted', $param))
	{
	    $where[] = "ci_view.deleted = false";
	}
	
        if (array_key_exists('exclude', $param)) {
	    
            list($id_arr_param, $id_arr_named) = db::in_list($param['exclude']);
            $where[] = "id not in ($id_arr_param)";
            $db_param = array_merge($db_param, $id_arr_named);
        }
			
        if (array_key_exists('filter_column', $param)) {
            $filter = $param['filter_column'];
					
            if (ciColumnType::getType($filter[0]) == CI_COLUMN_LIST) 
                {
                    $join .= "
join ci_column cc
on cc.ci_id = ci_view.id and cc.ci_column_type_id = :column_type
join ci_column_list cl
on 
        case when cc.value != '' and cc.value is not null then 
                cast(cc.value as int) 
        else 
                null 
        end = cl.id
";
                    $column = "cl.name";
							
                }
            else 
                {
							
                    $join .= "
join ci_column cc
on cc.ci_id = ci_view.id and cc.ci_column_type_id = :column_type";
                    $column = "cc.value";

                }
					
            $i = 0;
					
            $where_parts=array();
					

            foreach(explode(' ', $filter[1]) as $val) 
                {
                    $where_parts[] = "lower($column) like lower(:filter_value_$i)";
                    $db_param[":filter_value_$i"] = "%$val%";
                    $i++;
							
                }
            $where[] = implode(" or ", $where_parts);
					
					
            $db_param[':column_type'] = $filter[0];
        }
        if (array_key_exists('filter_type', $param)) {
            $filter = $param['filter_type'];
					
            $where[] = "ci_type_id = :filter_type_id";
            $db_param[':filter_type_id'] = $filter;
        }
		
        $where_str = "";
        if (count($where)) {
            $where_str = "where " . implode(' and ', $where);
        }

	
	$query = "
select ci_view.*, extract(epoch from log.update_time) as update_time 
from ci_view 
left join 
(
		select max(create_time) as update_time, ci_id 
		from ci_log group by ci_id
) log 
on log.ci_id = ci_view.id 
$join 
$where_str
order by id";
        
        if(array_key_exists('count', $param)) {
            $query = "select count(*) from ($query) count_me";
            $res = db::fetchItem($query, $db_param);
            return $res;
            
        }
        

        if (array_key_exists('limit', $param)) {
            $limit = "limit " . $param['limit'];
        }
			
        if (array_key_exists('offset', $param)) {
            $offset = "offset " . $param['offset'];
        }
			
        $query .= "
$limit 
$offset";
        
	//echo($query);
	//var_dump($db_param);
	
        $arr = db::fetchList($query, $db_param);
			
        if(!count($arr)){
            return array();
        }
        			
        $col_id_arr = array();
			
        $out = array();
        foreach( $arr as $row) {
            $ci = new ci();
            $ci->initFromArray($row);
            ci::$_cache[$ci->id] = $ci;
					
            $out[$row['id']] = $ci;
            $col_id_arr[] = $row['id'];
        }
	
        list($col_id_arr_param, $col_id_arr_named) = db::in_list($col_id_arr);
	
        $arr2 = db::fetchList("
select * 
from ci_column_view 
where id in ($col_id_arr_param) 
order by name", $col_id_arr_named);
        
        foreach( $arr2 as $row) {
            $out[$row['id']]->_ci_column[$row['column_type_id']] = $row['value'];
            $out[$row['id']]->applyAll();
        }
			
        return $out;
    }
}


class ciUser
extends dbItem
{
    static $me;
    var $id;
    var $username;
    var $fullname;
    var $password;
    var $email;
    var $deleted;
    
    function init()
    {
        ciUser::$me = new ciUser();
        $user_list = db::fetchList('select * from ci_user limit 1');
        ciUser::$me->initFromArray($user_list[0]);
        return true;
    }
    
}


class Property
{
    static $data=null;
    
    function load()
    {
        if (self::$data){
            return;
        }
        
        foreach(db::fetchList('select name, value from ci_property') as $row) {
            self::$data[$row['name']] = $row['value'];
        }
    }

    function get($name, $default=null) 
    {
        self::load();

        if (array_key_exists($name, self::$data)) {
            return @self::$data[$name];
        }
        return $default;
    }
    
    function set($name, $value) 
    {
        self::load();
        $param = array(":name"=>$name, ":value" => $value);
        if (array_key_exists($name, self::$data)) {
            if($value != self::$data[$name]) {
                db::query('update ci_property set value=:value where name=:name', $param);
            }
        } else {
            db::query('insert into ci_property (name, value) values (:name, :value)', $param);
        }
        self::$data[$name] = $value;
    }

}

class CiGraphCache
{
    
    function get($name) 
    {
        return db::fetchItem("select value from ci_graph_cache where key=:key",
                             array(":key"=>$name));
    }
    
    function set($name, $value) 
    {
        db::query("update ci_graph_cache set value=:value where key=:key",
                  array(":key"=>$name, ":value"=>$value));
        if( !db::count()) {
            db::query("insert into ci_graph_cache (key, value) values (:key,:value)",
                      array(":key"=>$name, ":value"=>$value));
        }
                

    }
}


class Event
{
    static $data=null;
    
    /**
     Load all events. Called automatically when needed, never call manually.
     */
    function _load()
    {
        if (is_array(self::$data)){
            return;
        }
        
        self::$data=array();
        
        foreach(db::fetchList('select event_name, class_name from ci_event') as $row) {
            if (!array_key_exists($row['event_name'],self::$data)) {
                self::$data[strToLower($row['event_name'])]=array();
            }
            self::$data[strToLower($row['event_name'])][] =  $row;
        }
    }

    /**
     Emit the specified event. Send the specified parameters to all even handlers.
    */
    function emit($name, $param) 
    {
        
        self::_load();
        $ev = self::$data[strToLower($name)];
        $param['event'] = $name;
        
        if ($ev) {
            
            foreach($ev as $item) {
                
                $class_str = $item['class_name'];
                
                $method_str = $name."Handler";
                util::loadClass($class_str);
                
                eval("$class_str::$method_str(\$param);");
            }
        }
    }
    
    /**
     Register a new event handler. 
    */
    function register($event_name, $class_name) 
    {
        echo "ERROR - Registering new events not yet implemented!";
    }
    
}

?>