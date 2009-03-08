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
			array(makeLink("?controller=ciType", "Edit CI types", null),
			      makeLink("?controller=ciColumn", "Edit CI columns", null),
			      makeLink("?controller=ciProperty", "Edit properties", null)),
			$content);

	}
	
	
	function viewWrite()
	{
		util::setTitle("Administration");
		$content .= "<p>";
                
		$content .= "This is the adminitration section of FreeCMDB. Use it to change what types of CI to model, and what data should be available for each CI.";
		$content .= "<ul><li>".makeLink("?controller=ciType", "Edit CI types", null)."</li>";
		$content .= "<li>".makeLink("?controller=ciColumn", "Edit CI columns", null)."</li>";
		$content .= "<li>".makeLink("?controller=ciProperty", "Edit properties", null)."</li>";
		$content .= "</ul>";
                $content .= "</p>";
	

		$this->show($content);
	}
	
    function isAdmin() 
    {
	    return true;
    }
    
}

?>