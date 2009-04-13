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
        
        $new_id = $this->createColumn();
        $ok &= ($new_id !== false);
	
	for ($idx=0;param("id_$idx")!==null;$idx++) {
            $ok &= $this->updateColumn(param("id_$idx"),
                                       param("type_$idx"),
                                       param("name_$idx"),
                                       param("ci_type_$idx"));
        }
        
        if ($ok) {
            $default = param('default');
            if (($default == 'new' && $new_id !== false && $new_id !== true) || $default >= 0) {
                Property::set("ciColumn.default",$default=="new"?$new_id:$default);
            } else {
                error("Invalid default column");
                $ok = false;
            }
        }
	
        if ($ok) {
            db::commit();
            message("Columns updated");
            redirect(makeUrl(array('controller'=>'ciColumn','task'=>null)));
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
	if (!cicolumnType::update($id, null, null, null, 1)) {
            error("Column could not be found, not removed.");
	} else {
	    message("Column removed");
	}
	
        redirect(makeUrl(array('controller'=>'ciColumn', 'task'=>'view', 'id'=>null)));
    }
    
    
    function viewRun() 
    {
	$this->render("ciColumn");
    }
	

}

?>