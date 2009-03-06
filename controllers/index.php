<?php

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


class CsvController
extends Controller
{
    
    function viewWrite()
    {
        
    }

}


?>