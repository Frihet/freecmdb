<?php

require_once('common/index.php');
require_once("model.php");

class MyApp 
extends Application
{

    function __construct()
    {
        $this->addScript('static/FreeCMDB.js');
        $this->addStyle('static/FreeCDMB.css');
    }
    
    
    /**
     Write out the top menu.
    */    
    function writeMenu($controller)
    {
        echo "
        <div class='main_menu no_print'>
        <iframe src='/tuit/menu/' width='100%' height='80px' frameborder='no'></iframe>
        </div>
";
        return;
        

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
    
    function preRun($controller)
    {
        Event::emit("Startup",array("source"=>$controller));	
        ciUser::assert_view();
    }
    
    function postRun($controller)
    {
        Event::emit("Shutdown",array("source"=>$controller));
    }
    
}

ciUser::init() || die("Login error.");
util::$path = Property::get("core.baseUrl","");

class FreeCMDB
{
    function dateTime($tm)
    {
        return date(Property::get("core.dateTimeFormat","Y-M-D H:i"), $tm);
    }
    
    function date($tm)
    {
        return date(Property::get("core.dateFormat","Y-M-D"), $tm);
    }
    

}
    
if (Property::get('core.locale',null)!=null){
    setlocale(LC_ALL,Property::get('core.locale',null));
// Specify location of translation tables
    bindtextdomain("FreeCMDB", "./locale");
    bind_textdomain_codeset("FreeCMDB", 'UTF-8'); 

// Choose domain
    textdomain("FreeCMDB");
}

$app = new MyApp();
$app->main();



?>