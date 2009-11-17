<?php
  /**
   Controller for performing a drilldown
  */
class DrilldownController
extends CmdbController
{
 
    function isEmbeded()
    {
	return param('update_target',null)!=null;
    }

    function updateTarget()
    {
	return param('update_target');
    }
    
    function embedPoint()
    {
	return param('embed_point','drilldown_root');
    }
    

    /**
     Returns the root of the drilldown tree
    */
    function getRoot()
    {
        $ci_all = Ci::fetch();
        return $ci_all[Property::get('plugin.drilldown.root')];
    }

    function viewRun()
    {
        $this->render("drilldown");
    }

    /**
     Returns a drilldown tree of all CIs
    */
    function getDrilldownInfo()
    {
        $ci_all = Ci::fetch();

	$res = array();

        foreach($ci_all as $ci) {
	    $set = array();
	    foreach($ci->getDirectDependants() as $child) {
		$set[$child->id] = 1;
	    }
	    foreach($ci->getDirectDependencies() as $child) {
		$set[$child->id] = 1;
	    }
	    
	    $res[$ci->id] = array("name"=>$ci->getDescription(), "children"=>array_keys($set), "id"=>$ci->id);
	    
        }
        return $res;
    }
    	
}

?>
