<?php
require_once("controllers/adminController.php");

class CITypeController
extends adminController
{

    function createType()
    {
        $name = param('name_new');
        $shape = param('shape_new','box');
        
        if ($name=="") {
            return true;
        }
        
        if (ciType::getId($name) !== null) {
            error("Another CI type named $name already exists");
            return false;
        }
        return ciType::create($name, $shape);
    }

    function updateRun()
    {
        $ok = true;
	
        db::begin();
        
        $new_id = $this->createType();
        $ok &= ($new_id !== false);
        
        for ($idx=0;$ok && param("id_$idx")!==null;$idx++) {
            $ok &= $this->updateType(param("id_$idx"),
                                     param("name_$idx"),
                                     param("shape_$idx"));
        }
        
        if ($ok) {
            db::commit();
            message("Types updated");
            util::redirect(makeUrl(array('controller'=>'ciType','task'=>null)));
        } else {
            db::rollback();
            $this->viewRun();
        }
        
    }
    
    function updateType($id, $name, $shape) 
    {
        if (ciType::getId($name) !== null && ciType::getId($name) != $id) {
            error("Another CI type named $name already exists");
            return false;
        }
        else {
            if( ciType::getName($id) == $name && 
                ciType::getShape($id) == $shape) {
                return true;
            }
            
            $ok = ciType::update($id, $name, $shape, 0);
            if (!$ok) {
                error("CI type could not be found, not updated.");
            }
            return $ok;
        }
    }

    function removeRun()
    {
        $id = param('id');
        if (ciType::update($id, null, null, 1)) {
            message("CI type removed");
        }
        else {
            error("CI type could not be found, not removed.");
        }
        
        util::redirect(makeUrl(array('controller'=>'ciType', 'task'=>null,'id'=>null)));
    }
    

    function viewRun() 
    {
        $this->render("ciType");
    }
    
    
}

?>
