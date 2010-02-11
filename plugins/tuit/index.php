<?php

class tuitPlugin
{
    static $has_db=false;

    function getClosedStatus()
    {
        $closed_id = dbTuit::fetchItem("select value from ticket_property where name = 'issue_closed_id'");
        if($closed_id === null) {
            return "()";
        }
        
        return "(" . implode(",",json_decode($closed_id)) . ")";
    }
    


    function startupHandler($param)
    {
        $app = $param['source']->getApplication();
        
        $app->addScript('../static/tuit.js');
        $app->addScript('../tuit/ticket/i18n.js');
    }
    
    /**
     Add the issue count coumn to the ci list view
     */
    function ciListControllerViewHandler($param)
    {
	
        if(!tuitPlugin::initDb()) {
            return;
        }
		
        if($param['point'] == 'pre') {
            $source = $param["source"];
            $source->addColumn("tuit_tickets", _("Issue count"));
	    
            $ci_list = $source->get_ci_list();
            if(!count($ci_list)) {
                return;
            }
            
            $param = array();
            $id_list=array();
            $i=0;
            
            
            foreach($ci_list as $ci) {
                $name = ":dyn_var_" . $i++;
                $param[$name] = $ci->id;
                $id_list[] = $name;
                $ci->tuit_tickets = "<span class='numeric'>0</span>";
            }
            $id_list = implode(", ", $id_list);

            $closed_status = tuitPlugin::getClosedStatus();
            

            $q = "select
    d.ci_id, 
    count(d.issue_id) as count
from ticket_cidependency d
join ticket_issue i
    on d.issue_id = i.id 
where d.ci_id in ($id_list)
    and i.current_status_id not in $closed_status
group by d.ci_id
";
            
            $counts = dbTuit::fetchList($q, $param);
                        
            foreach($counts as $row) {
                $id = $row['ci_id'];
                $count = $row['count'];
                $ci_list[$id]->tuit_tickets = "<span class='numeric'>$count</span>";
            }
        }
    }

    /**
     Propagate CI changes
     */
    function ciControllerRemoveHandler($param)
    {
        if(!tuitPlugin::initDb()) {
            return;
        }		
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciTuitMapping::removeCi($source->getCi());
        }
    }
    
    /**
     Propagate CI changes
     */
    function ciControllerSaveAllHandler($param)
    {
        if(!tuitPlugin::initDb()) {
            return;
        }
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciTuitMapping::updateCi($source->getCi());
        }        
    }
    

    /**
     Propagate CI changes
     */
    function ciControllerUpdateFieldHandler($param)
    {
        if(!tuitPlugin::initDb()) {
            return;
        }
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciTuitMapping::updateCi($source->getCi());
        }        
    }
    

    /**
     Propagate CI changes
     */
    function ciControllerCopyHandler($param)
    {
        if(!tuitPlugin::initDb()) {
            return;
        }
		
    }
    

    /**
     Propagate CI changes
     */
    function ciControllerRevertHandler($param)
    {
        if(!tuitPlugin::initDb()) {
            return;
        }
		
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciTuitMapping::updateCi($source->getCi());
        }
    }
    
    /**
     Show all open issues when viewing a CI
     */
    function ciControllerViewHandler($param)
    {
        if($param['point'] == 'post') {
            return;
        }
        
        
        $source = $param["source"];
        $ci = $source->getCi();
        //$mapping = ciRtMapping::find($ci->id);

        if(!tuitPlugin::initDb()) {
            return;
        }

        //message(sprint_r(dbTuit::fetchList("SELECT * FROM Users")));
        tuitPlugin::setup();
        //ciRtMapping::update();

        $res = "
<tr><th colspan='3'>"._("Open issues associated with this CI")."</th></tr>
";
		
        $zero = true;
        
        
        foreach(ciTuitMapping::fetchTickets($ci->id) as $ticket) {
            $zero = false;
            $url = htmlEncode("/tuit/ticket/view/" . $ticket['id']);
            
            $res .= "<tr><td></td><td colspan='2'><a href='$url'>".$ticket['id']." - ".htmlEncode($ticket['subject']) . "</a></td></tr>\n";
        }
        
        if ($zero) {
            $res .= "<tr><td></td><td colspan='2'>"._("No tickets associated with this CI")."</td></tr>\n";
        }
        		
        $source->addContent("ci_table", $res);
		
    }

    /* Creates a new database class with the name dbTuit, connected to
     the tuit database. Returns true if successfull, false otherwise.
     */    
    function initDb() 
    {
        if (!Property::get("plugin.tuit.DSN"))
            return false;
        
        if (self::$has_db) {
            return true;
        }
        if(class_exists("dbTuit")) {
            return false;
        }

        dbMaker::makeDb("dbTuit");
        self::$has_db = dbTuit::init(Property::get("plugin.tuit.DSN"));
        return self::$has_db;
    }

    function setup()
    {
    }

}

class CiTuitMapping
{
    static $mapping = false;
    static $reverse_mapping = false;
	
    function fetchTickets($ci_id) 
    {
        $closed_status = tuitPlugin::getClosedStatus();
        return dbTuit::fetchList("
select 
    i.id, 
    i.subject 
from ticket_cidependency as d
join ticket_issue as i
    on d.issue_id = i.id
where ci_id= :ci_id
    and i.current_status_id not in $closed_status
", array(":ci_id"=>$ci_id, ':closed_status' => Property::get('plugin.tuit.closedId')));
    }

    function updateCi($ci) 
    {
        dbTuit::query("update ticket_cidependency set description = :subject where ci_id=:id",
                    array(":subject"=>$ci->getDescription(),
                          ":id"=>$rt_id));
    }
    
    function removeCi($ci) 
    {
        dbTuit::query("delete from ticket_cidependency where ci_id = :id",
                      array(":id"=>$ci->id));
    }
        
}

?>