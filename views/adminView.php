<?php

class adminView
extends View
{

    function render($controller)
    {
        util::setTitle("Administration");
        $content .= "<p>";
                
        $content .= "This is the adminitration section of FreeCMDB. Use it to change what types of CI to model, and what data should be available for each CI.";
        $content .= "<ul><li>".makeLink("?controller=ciType", "Update the list of different CI types in the database", null)."</li>";
        $content .= "<li>".makeLink("?controller=ciColumn", "Update the list of different columns available for each CI", null)."</li>";
        $content .= "<li>".makeLink("?controller=ciProperty", "Update common system properties", null)."</li>";
        $content .= "<li>".makeLink("?controller=plugin", "View, install and configure plugins", null)."</li>";
        $content .= "</ul>";
        $content .= "</p>";
		
        $controller->show($content);
    }

}

?>