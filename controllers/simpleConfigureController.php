<?php

require_once("controllers/adminController.php");

class simpleConfigureController
extends adminController
{

    function viewRun()
    {
        $this->render("configure");
    }
    
    function updateRun()
    {
        $property_list = $this->getPropertyNames();
        for ($idx=0;param("name_$idx")!==null;$idx++) {
            Property::set(param("name_$idx"), param("value_$idx"));
        }
        message("Plugin properties updated");
        util::redirect(makeUrl(array('task'=>null)));
    }
    
}

?>