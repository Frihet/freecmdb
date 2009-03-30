<?php

class pluginView
{
	
    function render($controller)
    {
        util::setTitle("Installed FreeCMDB plugins");
        $content = "        
<ul>
";
        
        foreach(db::fetchList("select * from ci_plugin") as $plugin) {
            $content .= "<li>" . makeLink(array("task"=>'configure', 'plugin'=>$plugin['name']), $plugin['name'] . " - " . $plugin['description']) . makeLink(array('controller' => 'plugin', 'plugin' => $plugin['name'],'task'=>'uninstall'),'Remove', 'remove', "Remove the Plugin",array('onclick'=>'return confirm("Are you sure?");'))."</li>";
        }
        $content .= "</ul>";

        $form .= "
Send this file: <input name='package_file' type='file' />
<button type='submit'>Install plugin</button>
";
        $content .= form::makeForm($form,array('MAX_FILE_SIZE'=>'1000','controller'=>'plugin','task'=>'install'),'post',true);
        
        $controller->show($content);
       
    }
}

?>