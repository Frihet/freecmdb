<?php

class pluginView
{
	
    function render($controller)
    {
        util::setTitle(_("Installed FreeCMDB plugins"));

	$plugins = array();
	foreach (scandir("./plugins/") as $plugin_name)
	    if ($plugin_name !== '.' && $plugin_name !== '..' && @stat("./plugins/{$plugin_name}/install.json")) {
	        $plugins[$plugin_name] = json_decode(file_get_contents("./plugins/{$plugin_name}/install.json"), true);
	        $plugins[$plugin_name]['name'] = $plugin_name;
	        $plugins[$plugin_name]['enabled'] = false;
	    }
       
	foreach(db::fetchList("select * from ci_plugin") as $plugin)
            $plugins[$plugin['name']]['enabled'] = true;


        $content = "
<table class='striped'>
 <tr><th colspan='2'>" . _("Enabled modules") . "</th></tr> 
";
        foreach($plugins as $plugin)
	    if ($plugin['enabled']) {
		$content .= "<tr><td>";
		$content .= makeLink(array("plugin"=>$plugin['name'], 'controller' => 'configure'), $plugin['name'] . " - " . $plugin['description']);
		$content .= "</td><td>";
                $content .= makeLink(array('controller' => 'plugin', 'plugin_name' => $plugin['name'],'task'=>'reenable'),
                         _('Re-install'),
                         'button reenable',
                         _("Re-install the Plugin"));
                $content .= makeLink(array('controller' => 'plugin', 'plugin_name' => $plugin['name'],'task'=>'disable'),
                         _('Disable'),
                         'button disable',
                         _("Disable the Plugin"));
		$content .= makeLink(array('controller' => 'plugin', 'plugin_name' => $plugin['name'],'task'=>'uninstall'),
			     _('Remove'),
			     'button remove',
			     _("Remove the Plugin"),
			     array('onclick'=>'return confirm("Are you sure?");'));
		$content .= "</td></tr>";
            }

        $content .= "
 <tr><th colspan='2'>" . _("Disabled modules") . "</th></tr> 
";
        foreach($plugins as $plugin)
	    if (!$plugin['enabled']) {
		$content .= "<tr><td>";
		$content .= makeLink(array("plugin"=>$plugin['name'], 'controller' => 'configure'), $plugin['name'] . " - " . $plugin['description']);
		$content .= "</td><td>";
                $content .= makeLink(array('controller' => 'plugin', 'plugin_name' => $plugin['name'],'task'=>'enable'),
                         _('Enable'),
                         'button enable',
                         _("Enable the Plugin"));
		$content .= makeLink(array('controller' => 'plugin', 'plugin_name' => $plugin['name'],'task'=>'uninstall'),
			     _('Remove'),
			     'button remove',
			     _("Remove the Plugin"),
			     array('onclick'=>'return confirm("Are you sure?");'));
		$content .= "</td></tr>";
	    }
        $content .= "</table>";

        $form .= _("Send this file").": <input name='package_file' type='file' />
<button type='submit'>"._("Install plugin")."</button>
";
        $content .= form::makeForm($form,array('MAX_FILE_SIZE'=>'1000','controller'=>'plugin','task'=>'install'),'post',true);
        
        $controller->show($content);
       
    }
}

?>