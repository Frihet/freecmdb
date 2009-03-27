<?php

class rtPlugin
{
    static $has_db=false;
    
    function ciControllerViewHandler($param)
    {
        
        if (!Property::get("rtPlugin.DSN"))
            return;
        
        $source = $param["source"];
        $ci = $source->getCi();
        $mapping = ciRtMapping::find($ci->id);
        

        rtPlugin::initDb();

        message(sprint_r(dbRt::fetchList("SELECT * FROM Users")));
		
    }
    
    function initDb() 
    {
        if (self::$has_db) {
            return;
        }

        self::$has_db = true;
        dbMaker::makeDb("dbRt");
        return dbRt::init(Property::get("rtPlugin.DSN"));
    }

}

class ciRtMapping
extends dbItem
{
    public $id;
    public $ci_id;
    public $rt_id;
    
    function find($id) 
    {
        return dbItem::find("ci_id", $id, "ciRtMapping", "ci_rt_mapping");
    }
}

?>