<?php

class pluginView
{
	
    function render($controller)
    {
        util::setTitle(_("Installed FreeCMDB plugins"));
        $content = "        
<ul>
";
        
        foreach(db::fetchList("select * from ci_plugin") as $plugin) {
            $content .= "<li>" . makeLink(array("plugin"=>$plugin['name'], 'controller' => 'configure'), 
                                          $plugin['name'] . " - " . $plugin['description']) . makeLink(array('controller' => 'plugin', 'plugin' => $plugin['name'],'task'=>'uninstall'),_('Remove'), 'remove', _("Remove the Plugin"),array('onclick'=>'return confirm("Are you sure?");'))."</li>";
        }
        $content .= "</ul>";

        $form .= _("Send this file").": <input name='package_file' type='file' />
<button type='submit'>"._("Install plugin")."</button>
";
        $content .= form::makeForm($form,array('MAX_FILE_SIZE'=>'1000','controller'=>'plugin','task'=>'install'),'post',true);
        
        $controller->show($content);
       
    }
}

?>