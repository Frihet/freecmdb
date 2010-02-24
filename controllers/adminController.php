<?php

/** Base class of all controllers in the admin section. All these controllers
 have a common, simple action menu, defined in this class.
 */
class adminController
extends CmdbController
{

    function show($content)
    {
        ciUser::assert_admin();
        Controller::show($this->getActionMenu(), $content);
    }
    
    function getActionMenu() 
    {
        return array(makeLink(makeUrl(array("controller"=>"ciType")), _("CI types"), null),
		     makeLink(makeUrl(array("controller"=>"ciColumn")), _("CI columns"), null),
		     makeLink(makeUrl(array("controller"=>"ciProperty")), _("Properties"), null),
                     makeLink(makeUrl(array("controller"=>"ciDependency")), _("Dependencies"), null),
                     makeLink(makeUrl(array("controller"=>"plugin")), _("Plugins"), null)/*,
                     makeLink(makeUrl(array("controller"=>"userAdmin")), _("Users"), null),
                     makeLink(makeUrl(array("controller"=>"userGroupAdmin")), _("User groups"), null)*/);
    }
    
    function viewRun()
    {
	$this->addContent('breadcrumb', makeLink(makeUrl(array()), _('Administration')));
        $this->render("admin");
        
    }
    
    function isAdmin() 
    {
	    return true;
    }
    
}

?>