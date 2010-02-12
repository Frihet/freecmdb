<?php

class tuitPlugin
{
    static $has_db=false;

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
                $param[$name] = $ci->id
                $id_list[] = $name;
                $ci->tuit_tickets = "<span class='numeric'>0</span>";
            }
            $id_list = implode(", ", $id_list);
            
            $q = "select
ci_id, count(issue_id) count
from ticket_cidependency
where ci_id in ($id_list)
group by ci_id
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
            //ciRtMapping::removeOne($source->getCi());
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
            //ciRtMapping::updateOne($source->getCi());
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
            //ciRtMapping::updateOne($source->getCi());
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
		
		
        if($param['point'] == 'post') {
            //ciRtMapping::update();
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
            //ciRtMapping::updateOne($source->getCi());
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
<tr><th colspan='3'>Issues associated with this CI</th></tr>
";
		
        $zero = true;
        
        
        foreach(ciTuitMapping::fetchTickets($ci->id) as $ticket) {
            $zero = false;
            $url = htmlEncode("/tuit/ticket/" . $ticket['id']);
            
            $res .= "<tr><td></td><td colspan='2'><a href='$url'>Issue #".$ticket['id'].": ".htmlEncode($ticket['subject']) . "</a></td></tr>\n";
        }
        
        if ($zero) {
            $res .= "<tr><td></td><td colspan='2'>No tickets associated with this CI</td></tr>\n";
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
    /*	
    function configure($controller)
    {
        switch(param("subtask", 'view')) 
            {
            case 'view':
                $controller->render("RtPlugin");
                break;
				
            case 'update':
                self::updateRun($controller);
                break;
				
            }
    }
    */
    /*
    function updateRun($controller)
    {
        $property_list = RtPlugin::getPropertyNames();
        for ($idx=0;param("name_$idx")!==null;$idx++) {
            Property::set(param("name_$idx"), param("value_$idx"));
        }
        message("RT plugin properties updated");
        redirect(makeUrl(array()));
    }
    */
    /*
    function getPropertyNames()
    {
        return array("plugin.tuit.DSN" => "DSN for RT database",
                     "plugin.tuit.RtUser" => "Username for RT user to use for creating tickets for CIs",
                     "plugin.tuit.RtName" => "Rt installation name",
                     "plugin.tuit.RtURL" => "URL to RT instance");
    }
    */	
}
/*
class RtPluginView
extends View
{
    function render($controller)
    {
        $form = "
<div class='button_list'><button>Update</button></div>
<table class='striped'>
<tr>
<th>
Name
</th><th>
Value
</th></tr>
";
		
        $idx = 0;
        $property_list = RtPlugin::getPropertyNames();
            
        foreach($property_list as $name => $desc) {
            
            $value = Property::get($name);
            
            $form .= "<tr>";
            $form .= "<td>";
            
            $form .= "<input type='hidden' name='name_$idx' value='".htmlEncode($name)."'/>";
            $form .= htmlEncode($desc);
            
            $form .= "</td><td>";
            $form .= "<input name='value_$idx' value='".htmlEncode($value)."'/>";
            
            $form .= "</td></tr>";
            
            $idx++;
        }
        
        $form .= "</table>";
        $form .= "<div class='button_list'><button>Update</button></div>";
	
        $content .= form::makeForm($form,array('subtask'=>'update','task'=>'configure','plugin'=>'rt', 'controller'=>'plugin'));
        
        $controller->show($content);
	
    }
	

}
*/


class CiTuitMapping
{
    static $mapping = false;
    static $reverse_mapping = false;
	
    function fetchTickets($ci_id) 
    {
        return dbTuit::fetchList("
select i.id, i.subject 
from ticket_cidependency as d
join ticket_issue as i
on d.issue_id = i.id
where ci_id= :ci_id", array(":ci_id"=>$ci_id));
    }

    function updateOne($ci) 
    {
        /*
        $rt_id = db::fetchItem("select rt_id from ci_rt_mapping where ci_id = :id", 
                               array(":id" => $ci->id));
        dbTuit::query("update Tickets set Subject=:subject where id=:id",
                    array(":subject"=>$ci->getDescription(),
                          ":id"=>$rt_id));
        */
    }
    
    function removeOne($ci) 
    {
        /*
        $rt_id = db::fetchItem("select rt_id from ci_rt_mapping where ci_id = :id", 
                               array(":id" => $ci->id));
        
        dbTuit::query("delete from Tickets where id = :id",
                    array(":id"=>$rt_id));
        */
    }
        
    function update() 
    {
        /*
        foreach( db::fetchList("select id from ci where deleted=false and id not in (select ci_id from ci_rt_mapping)") as $row) 
            {
                $id = $row['id'];        
                
                $ci = ci::fetch(array("id_arr"=>array($id)));
                $ci = $ci[$id];
			
                dbTuit::begin();

                dbTuit::query("
insert into Tickets 
(
	Status, Queue, Type, Owner, Subject, LastUpdatedBy, LastUpdated, Creator, Created
) 
select 'new', :queue, 'ticket', id, :subject, id, now(), id, now() 
from Users 
where Name=:rt_user 
", array(":queue"=>Property::get("plugin.tuit.QueueId"),
         ":rt_user"=>Property::get("plugin.tuit.RtUser"),
         ":subject"=>$ci->getDescription()));
                $rt_id = dbTuit::lastInsertId("Tickets_id_seq");
                dbTuit::query("update Tickets set EffectiveId = id where id = :id", array(":id"=>$rt_id));
                
                if(dbTuit::count())
                    {
                        db::begin();
				
                        if(db::query("
insert into ci_rt_mapping
(ci_id, rt_id)
values
(:ci_id, :rt_id)",
                                     array(":ci_id"=>$ci->id,
                                           ":rt_id"=>$rt_id)))
                            {
                                dbTuit::commit();
                                db::commit();
					
                            }
                        else 
                            {
                                dbTuit::rollback();
                                db::rollback();
                                message("Failed to store ci mapping");
                            }
                    }
                else 
                    {
                        message("Failed to store rt mapping");
				
                        dbTuit::rollback();
                    }
			
			

            }
        */	
    }
	
    
    function load()
    {
        /*
        if (self::$mapping) 
            {
                return;
            }
		
        foreach( db::fetchList("select ci_id, rt_id from ci_rt_mapping") as $row) {
            self::$mapping[$row['ci_id']] = $row['rt_id'];
            self::$reverse_mapping[$row['rt_id']] = $row['ci_id'];
        }
        */
    }

	
}

?>