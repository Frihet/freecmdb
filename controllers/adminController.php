<?php

/** Base class of all controllers in the admin section. All these controllers
 have a common, simple action menu, defined in this class.
 */
class adminController
extends Controller
{

    function show($content)
    {
        Controller::show($this->getActionMenu(), $content);
    }
    
    function getActionMenu() 
    {
        return array(makeLink(makeUrl(array("controller"=>"ciType")), "CI types", null),
                     makeLink(makeUrl(array("controller"=>"ciColumn")), "CI columns", null),
                     makeLink(makeUrl(array("controller"=>"ciProperty")), "Properties", null),
                     makeLink(makeUrl(array("controller"=>"ciDependency")), "Dependencies", null),
                     makeLink(makeUrl(array("controller"=>"plugin")), "Plugins", null),
                     makeLink(makeUrl(array("controller"=>"userAdmin")), "Users", null),
                     makeLink(makeUrl(array("controller"=>"userGroupAdmin")), "User groups", null));
    }
    
    function viewRun()
    {
        $this->render("admin");
        
    }
    
    function isAdmin() 
    {
	    return true;
    }
    
}

?>