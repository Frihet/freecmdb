<?php

require_once('controllers/ci_controller.php');
require_once('controllers/ci_list_controller.php');
require_once('controllers/ci_type_controller.php');
require_once('controllers/ci_column_controller.php');

/**
 Base class for all controllers. 

 A controller should always be usade by first constructing it and then
 calling the run method. This method will dispatch the correct
 controller code by checking what parameters it was sent.
 
 Specifically, run will check the value of the $_REQUEST parameter
 task, and check if a method exists named in the same way, but with a
 'Write' suffix exists. For example, to implement a 'save' task, a
 developer needs to implement a 'saveWrite' method.

*/
class Controller
{

    /** Check the task param and try to run the corresponding
     function, if it exists. Gives an error otherwise.
    */
    function run() 
    {
        $task = param('task','view');
        
        $str = "{$task}Write";
        if(method_exists($this, $str)) {
            $this->$str();
        }
        else {
            echo "Unknown task: $task";
        }
    }

    /**
     Output a correctly formated action menu, given a set of links as input.
    */
    function actionMenu($link_list) 
    {
        echo "<div class='action_menu'>\n";
        echo "<ul>\n";
        if( count($link_list)) {
            echo  "<li><h2>Actions</h2></li>\n";
        
            foreach($link_list as $link) {
                
                echo "<li>";
                echo $link;
                echo "</li>\n";
            }
        }
        
        echo $this->action_box();

        echo "</ul>\n";
        echo "</div>\n";
					
    }

    /** A function to output the basic page layout given a set of menu
     items and content for the main pane.
    */
    function show($action_menu, $content)
    {
        $this->actionMenu($action_menu);

        echo "<div class='content'>";
        echo "<div class='content_inner'>";
		
        echo $content;


        echo "<div class='content_post'>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

    /** Create a little box with misc information for the botton od
     the action menu. Currently, this box only contains the ten laat
     edited CIs.
     */
    function action_box() 
    {
        if(param('revision_id') === null && !$this->isAdmin() && !$this->isHelp()) {
            
            $latest_id = log::getLatestIds();
            
            foreach($latest_id as $row) {
                $id_arr[] = $row['ci_id'];
            }

            //var_dump($latest_id);

            /*
             Don't show latest revisions when in history mode - it's confusing to have two different timelines...
            */
                    
            $items = ci::fetch(array('id_arr'=>$id_arr));
            $res .= "\n<li><h2>Latest edits</h2></li>\n\n";
            if (!$id_arr) {
                return "";
            }
            
            foreach($id_arr as $id) {
                $ci = $items[$id];
                if ($ci) {
                    $res .= "<li>";
                    $res .= makeLink(makeUrl(array('controller'=>'ci', 'id'=>$ci->id, 'task'=>null)), $ci->getDescription());
                    $res .= "</li>\n";
                }
            }
        }
        
        return $res;
    }
    
    function isAdmin() 
    {
	    return false;
    }
    
    function isHelp() 
    {
	    return false;
    }
    
	
}

/** Base class of all controllers in the admin section. All these controllers
 have a common, simple action menu, defined in this class.
 */
class AdminController
extends Controller
{

	function show($content)
	{
		Controller::show(
			array(makeLink("?controller=ciType", "Edit CI types", null),
			      makeLink("?controller=ciColumn", "Edit CI columns", null)),
			$content);

	}
	
	
	function viewWrite()
	{
		$title = "Administration";
		
                util::setTitle($title);
		$content .= "<h1>$title</h1>";
                $content .= "<p>";
                
		$content .= "This is the adminitration section of FreeCMDB. Use it to change what types of CI to model, and what data should be available for each CI.";
		$content .= "<ul><li>".makeLink("?controller=ciType", "Edit CI types", null)."</li>";
		$content .= "<li>".makeLink("?controller=ciColumn", "Edit CI columns", null)."</li>";
		$content .= "</ul>";
                $content .= "</p>";
	

		$this->show($content);
	}
	
    function isAdmin() 
    {
	    return true;
    }
    
}

/**
 The help page
 */
class HelpController
extends Controller
{
	
    private $content="";
    private $action_items=array();
    

    function add($header, $text)
    {
        $count = count($this->action_items);

        $this->content .= "<h2><a name='$count'>$header</a></h2> $text";
        $this->action_items[] = "<a href='#$count'>".$header."</a>";
    }
    
    function getContent()
    {
        return $this->content;
    }
    
    function getActionMenu()
    {
        return $this->action_items;
    }
    

    function viewWrite()
    {
        
            $this->add("Welcome to FreeCMDB", "
<p>
FreeCMDB is a Configuration Managment DataBase (CMDB), which is a tool
for tracking important assets in an IT environment. It is intended to
be used to track such things as servers and software services running
on various servers, but can also be used for other tasks such as
general purpose asset managment.
</p>
");
            $this->add("Page layout", "
<p>
Each FreeCMDB page consists of the same parts. These are:

<ul>

<li>The top menu, located at the very top. The items in this menu are always the same, and they take you to the different overall sections of FreeCMDB. These sections are:
<ul>
<li>Items, the main function of the program, where you view and edit your CIs.</li>
<li>Administration, where you can define new CI types, add or remove columns from CIs, etc.</li>
<li>Help, the FreeCMDB documentstion</li>
</ul>
</li>

<li>Messages, located just below the top menu. If there are any
messages from FreeCMDB, such as error reports or information on
actions taken, they are diplayed here. If there are no messages to
show, this section will be invisible. </li>

<li>Action bar, located on the left hand side of the screen. This bar
will contain the action menu, which is a menu which will contain
different items depending on which page you are viewing. The action
bar may also contain other items, such as a list of recently performed
actions.</li>

<li>Main content area, which is the main part of the application.</li>

</ul>
</p>
");

            $this->add("Installation","
TODO
");
            
            $this->add("Handling CIs","
<p> To start using FreeCMDB, first install the software (see
installation instructions), then go to the web page you have
configured for FreeCMDB. Click the 'Administration' link at the top of
the page, and then use the 'Edit CI types' and 'Edit CI columns' to
configure what types of configuration items you want, and what type of
data should be stored for your CIs.  </p>

<p> The next step is to start adding your CIs. Click on the 'Items'
link at the top left of the page. Then click on the 'Create new item'
link in the action menu. Enter information about your CI, and click
'Save'. Repeat these steps for every CI you want to add. Once you have
several CIs, you can also start creating dependencies between CIs in
order to see the full dependency tree of your CMDB.  </p>
");

            $this->add("Limitations", "
<p> 

<ul>

<li>
FreeCMDBuses creates graphs in the SVG format to show all
dependencies in the system. The SVG file format is not currently
supported by Internet Explorer. FreeCMDB falls back to rendering the
grapgs in the PNG format on browsers that dfo not support
SVG. Unfortunalt,y the SVG format does not have support for links in
the graph, and rendering quality is overall lower. It is therefor
strongly suggeted that you use a better browser, such as 
<a href='http://mozilla.org'>Mozilla Firefox</a>, when using
FreeCMDB. 
</li>

<li> FreeCMDB does not currently support user managment. To restrict
access to it, use a .htaccess file to set a password.  </li>

</ul>
</p>
");
            
                       
            $this->show($this->getActionMenu(),$this->getContent());
		
	}
	

	function isHelp()
	{
		return true;
		
	}
	
}

/**
 Ajax-controller for updating the contrnts of list-style columns.
 */
class FormController
extends Controller
{
    function addColumnListItemWrite() 
    {
        $column_id=param('column_id');
        $value=param('value');
        
        
        ciColumnList::addItem($column_id, $value);
                
        $this->viewWrite();
    }

    function updateColumnListItemWrite() 
    {
        $id=param('id');
        $value=param('value');
        ciColumnList::updateItem($id, $value);			
        $this->viewWrite();
    }

    function removeColumnListItemWrite() 
    {
        $id=param('id');
        $column_id=param('column_id');
        ciColumnList::removeItem($id, $column_id);        
        $this->viewWrite();
    }
    
    function viewWrite()
    {
        ob_end_clean();
        foreach(ciColumnList::getItems(param('column_id')) as $id => $name) {
            echo "$id\t$name\n";
        }
        exit(0);
    }

    function fetchListTableWrite()
    {
        ob_end_clean();
        echo form::makeColumnListEditor(param('column_id'), param('select_id'), param('table_id'));
        exit(0);
    }

}


class CsvController
extends Controller
{
    
    function viewWrite()
    {
        
    }

}


?>