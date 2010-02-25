<?php

require_once("controllers/adminController.php");

/** A very simple controller for displaying a minimal configuration
 interface.
 */
class simpleConfigureController
extends adminController
{
    
    function viewRun()
    {
	$this->addContent('breadcrumb', makeLink(makeUrl(array('controller'=>'plugin')), _('Plugins')));
	$this->addContent('breadcrumb', makeLink(makeUrl(array()), param('plugin')));
        $this->render("configure");
    }
    
    function updateRun()
    {
        $property_list = $this->getPropertyNames();
        for ($idx=0;param("name_$idx")!==null;$idx++) {
            Property::set(param("name_$idx"), param("value_$idx"));
        }
        message(_("Plugin properties updated"));
        util::redirect(makeUrl(array('plugin'=>param('plugin'),
                                     'controller'=>param('controller'))));
    }
    
}

?>