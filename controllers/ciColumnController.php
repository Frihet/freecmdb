<?php

require_once("controllers/adminController.php");


class CiColumnController
extends adminController
{

    function createColumn()
    {

        $name = param('name_new');
        $type = param('type_new',0);

		if (strlen($name) && $type !== null) 
		{
			
			if (ciColumnType::getId($name) !== null) {
				error("Another CI type named $name already exists");
				return false;
			}
			else {
				db::query("insert into ci_column_type (name, type) values (:name, :type)",
						  array(':name'=>$name, ':type'=>$type));
				if (db::count()) {
					message("Column type created");
				}
				else {
					error("Column type could not be created.");
					return false;
				}
				return db::lastInsertId("ci_column_type_id_seq");
				
			}
		}

		return true;
		
    }

    function updateWrite()
    {
		$ok = true;
		
		db::begin();

		$new_id = $this->createColumn();
		$ok &= ($new_id !== false);
		

		for ($idx=0;param("id_$idx")!==null;$idx++) 
		{
			$ok &= $this->updateColumn(param("id_$idx"),
									   param("type_$idx"),
									   param("name_$idx"));
		}

		if ($ok) 
		{
			$default = param('default');
			if (($default == 'new' && $new_id !== false && $new_id !== true) || $default >= 0) {
				Property::set("ciColumn.default",$default=="new"?$new_id:$default);
			}
			else 
			{
				error("Invalid default column");
				$ok = false;
			}
			
		}
		

		if ($ok) {
			

			db::commit();
			message("Columns updated");
			redirect(makeUrl(array('controller'=>'ciColumn','task'=>null)));
		}
		else {
			db::rollback();
			$this->viewWrite();
		}

	}


    function updateColumn($id, $type, $name)
    {
        if (ciColumnType::getId($name) !== null && ciColumnType::getId($name) != $id) {
            error("Another column named $name already exists");
			return false;
		}
        else {
			if( ciColumnType::getType($id) == $type && ciColumnType::getName($id) == $name) 
			{
				
				return true;
				
			}
            if (!cicolumnType::update($id, $name, $type, 0)) {
                error("Column type $type for column $name could not be found, not updated.");
				return false;
            }
        }
		return true;
    }

    function removeWrite()
    {
        $id = param('id');
		if (!cicolumnType::update($id, null, null, 1)) {
            error("Column could not be found, not removed.");
		} else {
			message("Column removed");
        }

        redirect(makeUrl(array('controller'=>'ciColumn', 'task'=>'view', 'id'=>null)));
    }
    

    function viewWrite() 
    {
		$this->render("ciColumn");
	}
	

}

?>