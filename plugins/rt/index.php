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
		
		foreach(ciRtMapping::fetchTickets($ci->id) as $ticket) 
		{
			$url = htmlEncode(Property::get("rtPlugin.RtURL")."/Ticket/Display.html?id=" . $ticket['id']);
			
			$res .= "<tr><td></td><td colspan='2'><a href='$url'>Issue #".$ticket['id'].": ".htmlEncode($ticket['subject']) . "</a></td></tr>\n";
		}

		
		$source->addContent("ci_table", $res);
		
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

	function setup()
    {
//		dbRt::fetchList("select id from Queue where name");
		if (!Property::get("rtPlugin.QueueId")) 
		{
			if (dbRt::query("insert into Queues (Name, Description) values (:name, :description)", array(":name"=>"FreeCMDB CIs", ":description"=>"An automatically generated queue listing all configuration items in FreeCMDB, used to track dependencies between CIs and tickets"))) 
			{
				$id = dbRt::lastInsertId("Queues_id_seq");
				Property::set("rtPlugin.QueueId", $id);
				message("Created queue with id $id");
			}
			
		}
		

    }
	

}

class ciRtMapping
{
	static $mapping = false;
	
	function fetchTickets($ci_id) 
	{
		$rt_id = db::fetchItem("select rt_id from ci_rt_mapping where ci_id = :ci_id", array(":ci_id"=>$ci_id));
		return dbRt::fetchList("select Tickets.id as id, Subject as subject from Links join Tickets on Target = concat('fsck.com-rt://',:rt_name,'/ticket/', Tickets.id) where Links.Base like :id and Links.Type = 'DependsOn'", 
							   array(":id"=>"fsck.com-rt://".Property::get("rtPlugin.RtName")."/ticket/" . $rt_id,
								   ":rt_name"=>Property::get("rtPlugin.RtName")));
	}
	
    
    function update() 
    {
		foreach( db::fetchList("select id from ci where deleted=false and id not in (select ci_id from ci_rt_mapping)") as $row) 
		{
			$id = $row['id'];        $param=array();
        foreach($param_in as $key => $value) {
			$param[substr($key,1)] = $value;
        }

			$ci = ci::fetch(array("id_arr"=>array($id)));
			$ci = $ci[$id];
			
			dbRt::begin();

			dbRt::query("
insert into Tickets 
(
		queue, type, owner, subject, LastUpdatedBy, LastUpdated, Creator, Created
) 
select :queue, 'ticket', id, :subject, id, now(), id, now() 
from Users 
where Name=:rt_user 
", array(":queue"=>Property::get("rtPlugin.QueueId"),
		 ":rt_user"=>Property::get("rtPlugin.RtUser"),
		 ":subject"=>"CI: ". $ci->getDescription()));
			
			if(dbRt::count())
			{
				$rt_id = dbRt::lastInsertId(null);
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
	
	function load()
	{
		if (self::$mapping) 
		{
			return;
		}
		
		foreach( db::fetchList("select ci_id, rt_id from ci_rt_mapping") as $row) {
			self::$mapping[$row['ci_id']] = $row['rt_id'];
		}
	}

	
	
}

?>