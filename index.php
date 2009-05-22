<?php

require_once('common/index.php');
require_once("model.php");
class MyApp 
extends Application
{

    function __construct()
    {
        $this->addScript('static/FreeCMDB.js');
        //        $this->addStyle('static/FreeCDMB.css');
    }
    
    
    /**
     Write out the top menu.
    */    
    function writeMenu($controller)
    {
        $is_admin = $controller->isAdmin();
        $is_help = $controller->isHelp();
        $is_ci = !$is_admin && !$is_help;
	
        echo "<div class='main_menu'>\n";
        echo "<div class='main_menu_inner'>";
        echo "<div class='logo'><a href='?'>FreeCMDB</a></div>";
	
        echo "<ul>\n";
        
        echo "<li>";
        echo makeLink(makeUrl(array("controller"=>"ciList")), "Items", $is_ci?'selected':null);
        echo "</li>\n";
        
        echo "<li>";
        echo makeLink(makeUrl(array("controller"=>"admin")), "Administration", $is_admin?'selected':null);
        echo "</li>\n";
        
        echo "<li>";
        echo makeLink(makeUrl(array("controller"=>"help")), "Help", $is_help?'selected':null);
        
        /*
        echo "<li>";
        echo makeLink("?controller=logout", "Log out", null);
        echo "</li>\n";
        */        
        echo "</ul></div></div>\n";
    }

    function getDefaultController()
    {
        return "ciList";
    }
    
    function getApplicationName()
    {
        return "FreeCMDB";
    }
    
}

ciUser::init() || die("Login error.");
util::$path = Property::get("core.baseUrl","");

$app = new MyApp();
$app->main();

?>