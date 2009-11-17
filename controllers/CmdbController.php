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
    
}


class CsvController
extends CmdbController
{
    
    function viewRun()
    {
        
    }

}


?>