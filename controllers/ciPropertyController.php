<?php

require_once("controllers/adminController.php");

class CiPropertyController
extends adminController
{

    function updateRun()
    {
		db::begin();

		for ($idx=0;param("name_$idx")!==null;$idx++) 
		{
			Property::set(param("name_$idx"), param("value_$idx"));
		}

		db::commit();
		message("Propertys updated");
		util::redirect(makeUrl(array('controller'=>'ciProperty','task'=>null)));
		
	}


    function updateProperty($id, $type, $name)
    {
        if (ciPropertyType::getId($name) !== null && ciPropertyType::getId($name) != $id) {
            error("Another column named $name already exists");
			return false;
		}
        else {
			if( ciPropertyType::getType($id) == $type && ciPropertyType::getName($id) == $name) 
			{
				
				return true;
				
			}
            if (!cicolumnType::update($id, $name, $type, 0)) {
                error("Property type $type for column $name could not be found, not updated.");
				return false;
            }
        }
		return true;
    }

    function viewRun() 
    {
	$this->addContent('breadcrumb', makeLink(makeUrl(array()), _('Properties')));
	$this->render("ciProperty");
    }
	

}

?>
