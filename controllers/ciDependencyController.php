<?php

require_once("controllers/adminController.php");

class CiDependencyController
extends adminController
{

    function createDependency()
    {

        $name = param('name_new');
        $reverse_name = param('reverse_name_new');
        $color = param('color_new');

        if (strlen($name) ) {
            if ((ciDependencyType::getId($name) !== null) || ($reverse_name != "" && ciDependencyType::getId($reverse_name) !== null)) {
                error("Another CI dependency named $name already exists");
                return false;
            }
            return ciDependencyType::create($name, $reverse_name, $color);
        }
        return true;
    }
    

    function updateRun()
    {
        $ok = true;
	
        db::begin();
        
        $new_id = $this->createDependency();
        $ok &= ($new_id !== false);
	
        for ($idx=0;param("id_$idx")!==null;$idx++) {
            $ok &= $this->updateDependency(param("id_$idx"),
					   param("name_$idx"),
					   param("reverse_name_$idx"),
					   param("color_$idx"));
        }
        
        if ($ok) {
            $default = param('default');
            if (($default == 'new' && $new_id !== false && $new_id !== true) || $default >= 0) {
                Property::set("ciDependency.default",$default=="new"?$new_id:$default);
            } else {
                error("Invalid default dependency");
                $ok = false;
            }
        }
	
        if ($ok) {
            db::commit();
            message("Dependencies updated");
            util::redirect(makeUrl(array('controller'=>'ciDependency','task'=>null)));
        } else {
            db::rollback();
            $this->viewRun();
        }
        
    }


    function updateDependency($id, $name, $reverse_name, $color)
    {
        if (ciDependencyType::getId($name) !== null && ciDependencyType::getId($name) != $id) {
            error("Error while updating dependecy type with id $id: Another dependency named $name already exists, with id " .ciDependencyType::getId($name));
	    return false;
	}
        else if ($reverse_name != "" && ciDependencyType::getId($reverse_name) !== null && ciDependencyType::getId($reverse_name) != $id) {
            error("Error while updating dependecy type with id $id: Another dependency named $reverse_name already exists, with id " .ciDependencyType::getId($reverse_name));
	    return false;
	}
        else {
	    if( ciDependencyType::getName($id) == $name && 
		ciDependencyType::getReverseName($id) == $reverse_name &&
		ciDependencyType::getColor($id) == $color ) 
	    {				
		return true;
	    }
            if (!cidependencyType::update($id, $name, $reverse_name, $color, 0)) {
                error("Dependency $name could not be found, not updated.");
		return false;
            }
        }
	return true;
    }

    function removeRun()
    {
        $id = param('id');
	if (!ciDependencyType::update($id, null, null, null, 1)) {
            error("Dependency could not be found, not removed.");
	} else {
	    message("Dependency removed");
        }

        util::redirect(makeUrl(array('controller'=>'ciDependency', 'task'=>'view', 'id'=>null)));
    }
    

    function viewRun() 
    {
	$this->addContent('breadcrumb', makeLink(makeUrl(array('controller'=>'admin')), _('Administration')));
	$this->addContent('breadcrumb', makeLink(makeUrl(array()), _('Dependencies')));
	$this->render("ciDependency");
    }
	

}

?>