<?php

class rtPlugin
{
    static $has_db=false;

    /**
     Add the issue count coumn to the ci list view
     */
    function ciListControllerViewHandler($param)
    {
                
        if(!rtPlugin::initDb()) {
            return;
        }
		
        if($param['point'] == 'pre') {
            $source = $param["source"];
            $source->addColumn("tickets", "Issue count");

            $ci_list = $source->get_ci_list();
            if(!count($ci_list)) {
                return;
            }
            
            $param = array();
            $id_list=array();
            $i=0;
            
            foreach($ci_list as $ci) {
                $name = ":dyn_var_" . $i++;
                
                $rt_id = CiRtMapping::getRtId($ci->id);
                $param[$name] = "fsck.com-rt://".Property::get("rtPlugin.RtName")."/ticket/" . $rt_id;
                $id_list[] = $name;
                $ci->tickets = "<span class='numeric'>0</span>";
                
            }
            $id_list = implode(", ", $id_list);
            
            $q = "select
l.base base, count(t.id) count
from Links l
join Tickets t
on l.Target = concat('fsck.com-rt://', :rt_name, '/ticket/', t.id)
where l.Base in ($id_list) and l.Type = 'DependsOn' and status != 'closed'
group by l.Base
";

            $param[':rt_name'] = Property::get("rtPlugin.RtName");
            
            $counts = dbRt::fetchList($q, $param);

                        
            foreach($counts as $row) {
                $stuff = explode("/", $row['base']);
                $id = CiRtMapping::getCiId($stuff[count($stuff)-1]);
                $count = $row['count'];
                $ci_list[$id]->tickets = "<span class='numeric'>$count</span>";
            }
            
        }
    }

    /**
     Propagate CI changes
     */
    function ciControllerRemoveHandler($param)
    {
        if(!rtPlugin::initDb()) {
            return;
        }
		
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciRtMapping::removeOne($source->getCi());
        }
    }
    
    /**
     Propagate CI changes
     */
    function ciControllerSaveAllHandler($param)
    {
        if(!rtPlugin::initDb()) {
            return;
        }
		
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciRtMapping::updateOne($source->getCi());
        }        
    }
    

    /**
     Propagate CI changes
     */
    function ciControllerUpdateFieldHandler($param)
    {
        if(!rtPlugin::initDb()) {
            return;
        }
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciRtMapping::updateOne($source->getCi());
        }        
    }
    

    /**
     Propagate CI changes
     */
    function ciControllerCopyHandler($param)
    {
        if(!rtPlugin::initDb()) {
            return;
        }
		
		
        if($param['point'] == 'post') {
            ciRtMapping::update();
        }        
    }
    

    /**
     Propagate CI changes
     */
    function ciControllerRevertHandler($param)
    {
        if(!rtPlugin::initDb()) {
            return;
        }
		
		
        if($param['point'] == 'post') {
            $source = $param["source"];
            ciRtMapping::updateOne($source->getCi());
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

        if(!rtPlugin::initDb()) {
            return;
        }
		
		

        //message(sprint_r(dbRt::fetchList("SELECT * FROM Users")));
        rtPlugin::setup();
        ciRtMapping::update();

        $res = "
<tr><th colspan='3'>Issues associated with this CI</th></tr>
";
		
        $zero = true;
        
        
        foreach(ciRtMapping::fetchTickets($ci->id) as $ticket) {
            $zero = false;
            $url = htmlEncode(Property::get("rtPlugin.RtURL")."/Ticket/Display.html?id=" . $ticket['id']);
            
            $res .= "<tr><td></td><td colspan='2'><a href='$url'>Issue #".$ticket['id'].": ".htmlEncode($ticket['subject']) . "</a></td></tr>\n";
        }
        
        if ($zero) {
            $res .= "<tr><td></td><td colspan='2'>No tickets associated with this CI</td></tr>\n";
        }
        		
        $source->addContent("ci_table", $res);
		
    }
    
    function initDb() 
    {
        if (!Property::get("rtPlugin.DSN"))
            return false;
        
        if (self::$has_db) {
            return true;
        }
        if(class_exists("dbRt")) {
            return;
        }

        dbMaker::makeDb("dbRt");
        self::$has_db = dbRt::init(Property::get("rtPlugin.DSN"));
        return self::$has_db;

    }

    function setup()
    {
        if (!Property::get("rtPlugin.QueueId")) {
            if (dbRt::query("insert into Queues (Name, Description) values (:name, :description)", array(":name"=>"FreeCMDB CIs", ":description"=>"An automatically generated queue listing all configuration items in FreeCMDB, used to track dependencies between CIs and tickets"))) {
                $id = dbRt::lastInsertId("Queues_id_seq");
                Property::set("rtPlugin.QueueId", $id);
                message("Created message queue in RT with queue id $id");
            }
        }
    }
	
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

    function updateRun($controller)
    {
        $property_list = RtPlugin::getPropertyNames();
        for ($idx=0;param("name_$idx")!==null;$idx++) {
            Property::set(param("name_$idx"), param("value_$idx"));
        }
        message("RT plugin properties updated");
        redirect(makeUrl(array()));
    }

    function getPropertyNames()
    {
        return array("rtPlugin.DSN" => "DSN for RT database",
                     "rtPlugin.RtUser" => "Username for RT user to use for creating tickets for CIs",
                     "rtPlugin.RtName" => "Rt installation name",
                     "rtPlugin.RtURL" => "URL to RT instance");
    }
	
}

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



class CiRtMapping
{
    static $mapping = false;
    static $reverse_mapping = false;
	
    function fetchTickets($ci_id) 
    {
        $rt_id = db::fetchItem("select rt_id from ci_rt_mapping where ci_id = :ci_id", array(":ci_id"=>$ci_id));
        return dbRt::fetchList("
select Tickets.id as id, Subject as subject 
from Links 
join Tickets 
on Target = concat('fsck.com-rt://',:rt_name,'/ticket/', Tickets.id) 
where Links.Base like :id and Links.Type = 'DependsOn' and Tickets.status != 'closed'", 
                               array(":id"=>"fsck.com-rt://".Property::get("rtPlugin.RtName")."/ticket/" . $rt_id,
                                     ":rt_name"=>Property::get("rtPlugin.RtName")));
    }

    function updateOne($ci) 
    {
        $rt_id = db::fetchItem("select rt_id from ci_rt_mapping where ci_id = :id", 
                               array(":id" => $ci->id));
        dbRt::query("update Tickets set Subject=:subject where id=:id",
                    array(":subject"=>$ci->getDescription(),
                          ":id"=>$rt_id));
    }
    
    function removeOne($ci) 
    {
        $rt_id = db::fetchItem("select rt_id from ci_rt_mapping where ci_id = :id", 
                               array(":id" => $ci->id));
        
        dbRt::query("delete from Tickets where id = :id",
                    array(":id"=>$rt_id));
    }
        
    function update() 
    {
        foreach( db::fetchList("select id from ci where deleted=false and id not in (select ci_id from ci_rt_mapping)") as $row) 
            {
                $id = $row['id'];        
                
                $ci = ci::fetch(array("id_arr"=>array($id)));
                $ci = $ci[$id];
			
                dbRt::begin();

                dbRt::query("
insert into Tickets 
(
	Status, Queue, Type, Owner, Subject, LastUpdatedBy, LastUpdated, Creator, Created
) 
select 'new', :queue, 'ticket', id, :subject, id, now(), id, now() 
from Users 
where Name=:rt_user 
", array(":queue"=>Property::get("rtPlugin.QueueId"),
         ":rt_user"=>Property::get("rtPlugin.RtUser"),
         ":subject"=>$ci->getDescription()));
                $rt_id = dbRt::lastInsertId("Tickets_id_seq");
                dbRt::query("update Tickets set EffectiveId = id where id = :id", array(":id"=>$rt_id));
                
                if(dbRt::count())
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
                                dbRt::commit();
                                db::commit();
					
                            }
                        else 
                            {
                                dbRt::rollback();
                                db::rollback();
                                message("Failed to store ci mapping");
                            }
                    }
                else 
                    {
                        message("Failed to store rt mapping");
				
                        dbRt::rollback();
                    }
			
			

            }
		
    }
	
    function getRtId($ci_id) 
    {
        self::load();
        return self::$mapping[$ci_id];
    }
    
    function getCiId($rt_id) 
    {
        self::load();
        return self::$reverse_mapping[$rt_id];
    }
    

    function load()
    {
        if (self::$mapping) 
            {
                return;
            }
		
        foreach( db::fetchList("select ci_id, rt_id from ci_rt_mapping") as $row) {
            self::$mapping[$row['ci_id']] = $row['rt_id'];
            self::$reverse_mapping[$row['rt_id']] = $row['ci_id'];
        }
    }

	
}

?>