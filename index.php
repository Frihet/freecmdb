<?php

  /** Entry point for all FreeCMDB functinality except the charts,
   which live in a separate file.

   Code layout:

   model.php: All model code.

   db.php: Database abstraction.

   controllers/*.php: All controllers. As of today, view code is
   embedded in the controller code. That should be cleaned up.

   views/*.php: Currently empty, view code lives in the controllers.

   index.php: Glue code.

   install.php: Installation check code.

   util.php: Mish util functions.

   form.php: Form related util code.

   */

define("FREECMDB",1);

$start_time = microtime(true);

require_once("util/util.php");
require_once("util/db.php");
require_once("install.php");

require_once("config.php");
require_once("model.php");
require_once("util/form.php");
require_once("controllers/index.php");
require_once("views/index.php");

/** Main class. Responsible for a bit of scafolding around the page
 proper, like the top menu and the performance data at the
 bottom. Also responsible for locating the appropriate coltroller,
 initializing it and handing of control to it.
 */
class FreeCMDB
{
    /*
     Write http heades, html headers and the top menu
     */
    function writeHeader($title, $controller)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
                <link rel="stylesheet" href="static/FreeCMDB.css" type="text/css" media="screen,projection" />
                <script type="text/javascript" src="static/prototype.js"></script>
                <script type="text/javascript" src="static/FreeCMDB.js"></script>
                <script type="text/javascript" src="static/tiny_mce/tiny_mce.js"></script>
                <title>'.htmlEncode($title).'</title>
<script type="text/javascript">
tinyMCE.init({
mode : "specific_textareas",
editor_selector : "rich_edit",
theme : "simple"
/*,
skin : "fc"*/
});
</script>
        </head>
        <body>

';
        $this->writeMenu($controller);
        
        $this->writeMessages();
        

    }
    
    /**
     Write bottom content.
     */
    function writeFooter() 
    {
        global $start_time;
        $stop_time = microtime(true);
        
        $copyright = "© 2009 Freecode AS";
        $performance = "Page rendered in " . sprintf("%.2f", $stop_time - $start_time) . " seconds. " .db::$query_count . " database queries executed in " . sprintf("%.2f", db::$query_time) . " seconds.";
       
        echo "<div class='copyright'>\n";
        
        echo makeLink("http://www.freecode.no", $copyright, 'copyright_inner', $performance);
        echo "</div>\n";
        
        echo "<script>stripe();</script>
</body>
</html>
";

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
        echo "<ul>\n";
 
	        echo "<li>";
	echo makeLink("?controller=ciList", "Items", $is_ci?'selected':null);
        echo "</li>\n";

        echo "<li>";
	echo makeLink("?controller=admin", "Administration", $is_admin?'selected':null);
        echo "</li>\n";
        
        echo "<li>";
	echo makeLink("?controller=help", "Help", $is_help?'selected':null);

        /*
        echo "<li>";
        echo makeLink("?controller=logout", "Log out", null);
        echo "</li>\n";
        */        
        echo "</ul></div>\n";
    }

    /**
     Write out the message list.
     */
    function writeMessages()
    {
        $msg = messageGet();
        if ($msg != "") {
            echo "<div class='messages'>\n";
            echo "<div class='messages_inner'>\n";
            echo $msg;
            echo "</div>\n";
            echo "</div>\n";
        }
    }
    
    /**
     Main application runner.
     */
    function main() 
    {
        ob_start();

        util::setTitle("");
	$controller = null;
	
        try {
            
            $controller = param('controller',param('action','ciList'));

            $controller_str = "{$controller}Controller";
            /**
             Try and see if we have a controller with the specified name
            */
            if(class_exists($controller_str)) {
                $controller = new $controller_str();
                $controller->run();
            } else {
                header("Status: 404");
                echo "Controller $controller not found!";
            }
        }
        catch(PDOException $e) {
            echo "PDO Exception";
            echo $e->getMessage();
        }
        $out = ob_get_contents();
        ob_clean();
	
        $this->writeHeader("FreeCMDB - " . util::getTitle(), $controller);

        echo $out;
        
        $this->writeFooter();
        
        ob_end_flush();

    }
    
}


db::init(DB_DSN) || die("The site is down. Reason: Could not connect to the database.");
ciUser::init() || die("Login error.");

ob_start();
ob_implicit_flush(0);

$cmdb = new FreeCMDB();
$cmdb->main();

util::printGzippedPage();
                 
?>