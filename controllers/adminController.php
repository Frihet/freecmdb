<?php

/** Base class of all controllers in the admin section. All these controllers
 have a common, simple action menu, defined in this class.
 */
class adminController
extends Controller
{

	function show($content)
	{
		Controller::show(
			array(makeLink("?controller=ciType", "CI types", null),
			      makeLink("?controller=ciColumn", "CI columns", null),
			      makeLink("?controller=ciProperty", "Properties", null),
				  makeLink("?controller=plugin", "Plugins", null)),
			$content);

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