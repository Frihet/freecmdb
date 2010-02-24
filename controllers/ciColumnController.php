<?php

require_once("controllers/adminController.php");


class CiColumnController
extends adminController
{

    function createColumn()
    {

        $name = param('name_new');
        $type = param('type_new');
        $ci_type = param('ci_type_new');
	if ($ci_type == "") 
	{
	    $ci_type=null;
	}
	
        if (strlen($name) && $type !== null) {
            if (ciColumnType::getId($name) !== null) {
                error("Another CI type named $name already exists");
                return false;
            }
            return ciColumnType::create($name, $type, $ci_type);
        }
        return true;
    }
    

    function updateRun()
    {
        $ok = true;
	
        db::begin();
        $default = null;
        
        foreach(param("column", array()) as $idx => $column) {
            
            $obj = new CiColumnType($column);
            if($obj->ci_type_id == '') {
                $obj->ci_type_id = null;
            }
            
            if($obj->name != "") {
                $ok &= $obj->save();

                if (param('column_default') == $idx) {
                    $default = $obj->id;
                }
                
            }
        }
        
        if ($ok && $default !== null) {
            message("Set def to $default");
            
            Property::set("ciColumn.default",$default);
        }
	
        if ($ok) {
            db::commit();
            message("Columns updated");
            util::redirect(makeUrl(array('controller'=>'ciColumn','task'=>null)));
        } else {
            db::rollback();
            $this->viewRun();
        }
        
    }


    function updateColumn($id, $type, $name, $ci_type)
    {
        if (ciColumnType::getId($name) !== null && ciColumnType::getId($name) != $id) {
            error("Another column named $name already exists");
			return false;
		}
        else {
	    if( ciColumnType::getType($id) == $type && 
		ciColumnType::getName($id) == $name &&
		ciColumnType::getCiType($id) == $ci_type) 
	    {
		return true;
	    }
            if (!cicolumnType::update($id, $name, $type, $ci_type, 0)) {
                error("Column type $type for column $name could not be found, not updated.");
		return false;
            }
        }
	return true;
    }

    function removeRun()
    {
        $id = param('id');

        $ci_type = new ciColumnType($id);
        
	if (!$ci_type->delete()) {
            error("Column could not be found, not removed.");
	} else {
	    message("Column removed");
	}
	
        util::redirect(makeUrl(array('controller'=>'ciColumn', 'task'=>'view', 'id'=>null)));
    }
    
    
    function viewRun() 
    {
	$this->addContent('breadcrumb', makeLink(makeUrl(array('controller'=>'admin')), _('Administration')));
	$this->addContent('breadcrumb', makeLink(makeUrl(array()), _('CI-columns')));
	$this->render("ciColumn");
    }
	

}

?>