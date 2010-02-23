<?php
/******************************************************************************
 *
 * Copyright © 2010
 *
 * FreeCode Norway AS
 * Nydalsveien 30A, NO-0484 Oslo, Norway
 * Norway
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ******************************************************************************/

require_once('common/index.php');
require_once("model.php");

/**
 Default, simple dropdown list for selecting a CI
 */
class DefaultCiSelector
{
    function make($name, $selected)
    {
        $arr = array();
        $all_ci_list = ci::fetch();

        foreach($all_ci_list as $item) {
            $item_id = $item->id;
            /*
             if ($ci->isDirectDependant($item_id) || $ci->id == $item_id) {
             continue;
             }*/
            $arr[$item_id] = $item->getDescription();
        }
        return form::makeSelect($name, $arr, null);
    }

}

/**
 App class for FreeCMDB
 */
class MyApp 
extends Application
{

    private $ci_selector;
    

    function __construct()
    {
        $this->addScript('static/FreeCMDB.js');
        $this->addStyle('static/FreeCMDB.css');
        $this->enableDatePicker();
        $this->enableTinyMce();        
        
        $this->setCiSelector(new DefaultCiSelector());
    }
    
    function makeCiSelector($name, $selected)
    {
        return $this->ci_selector->make($name, $selected);
    }

    function setCiSelector($s)
    {
        $this->ci_selector = $s;
    }
    
    /**
     Write out the top menu.
    */
    function writeMenu($controller)
    {
        if(Property::get('tuit.enabled','0')=='1') {
            echo "<div class='main_menu no_print'>";
	    echo implode("",$controller->getContent("main_menu_pre"));
	    echo "<iframe src='/tuit/menu/' width='100%' height='80px' frameborder='0'></iframe>";
	    echo implode("",$controller->getContent("main_menu_post"));
	    echo "</div>";
            return;
        }

        $is_admin = $controller->isAdmin();
        $is_help = $controller->isHelp();
        $is_ci = !$is_admin && !$is_help;
	
        echo "<div class='main_menu'>\n";
	echo implode("",$controller->getContent("main_menu_pre"));
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
        echo "</ul></div>";
        echo implode("",$controller->getContent("main_menu_post"));
	echo "</div>\n";
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