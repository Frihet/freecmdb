<?php

/**
 Base class for all FreeCMDB controllers. Adds event tiggers.

*/

class CmdbController
extends Controller
{
    function preRun()
    {
        $class_name = get_class($this);
        $event_name = $class_name.ucfirst($task);
        Event::emit($event_name,array("source"=>$this, "point"=>"pre"));
        $task = ucwords(param('task','view'));
        Event::emit($event_name . $task,array("source"=>$this, "point"=>"pre"));
        
    }
    
    function postRun()
    {
        $class_name = get_class($this);
        $event_name = $class_name.ucfirst($task);
        $task = ucwords(param('task','view'));
        Event::emit($event_name . $task,array("source"=>$this, "point"=>"post"));
        Event::emit($event_name,array("source"=>$this, "point"=>"post"));
    }

    function show($action_menu, $content)
    {
        $this->actionMenu($action_menu);

        echo "
	 <div class='content'>
	  <div class='content_inner'>
	   <div class='widget widget_2'>";
        if (htmlEncode(util::getTitle()) != '') {
         echo "
	    <div class='widget_header'>
	     <h2>";
         echo htmlEncode(util::getTitle());
         echo "
             </h2>
	    </div>";
        }
        echo implode("",$this->getContent("content_pre"));
        echo $content;
        echo implode("",$this->getContent("content_post"));
        echo "
            <div class='content_post'>
           </div>";
	$this->getApplication()->writeFooter2();
        echo "
          </div>
         </div>";
    }
  
}


?>