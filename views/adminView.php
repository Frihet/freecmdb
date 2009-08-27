<?php

class adminView
extends View
{

    function render($controller)
    {
        util::setTitle("Administration");
        $content .= "<p>";
                
        $content .= "This is the adminitration section of FreeCMDB. Use it to change what types of CI to model, and what data should be available for each CI.";
        $content .= "<ul>";
        
        foreach($controller->getActionMenu() as $item) {
            $content .= "<li>$item</li>\n";
        }
        
        $content .= "</ul>";
        $content .= "</p>";
		
        $controller->show($content);
    }

}

?>