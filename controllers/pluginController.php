<?php

require_once("controllers/adminController.php");

class PluginController
extends AdminController
{

    function viewRun() 
    {
	$this->addContent('breadcrumb', makeLink(makeUrl(array()), _('Plugins')));
        $this->render("plugin");
    }
	
    function configureRun()
    {
        $plugin = param("plugin_name");
		
        if(!$plugin) 
            {
                error(_("No plugin specified"));
                return;
            }
        if(!preg_match('/^[a-z][a-z0-9]*$/i', $plugin)) 
            {
                error(_("Illegal plugin name"));
                return;
            }

        util::loadClass($plugin."Plugin");

        util::setTitle(sprintf(_("Plugin configuration - %s"),$plugin));
		
        eval($plugin."Plugin::configure(\$this);");
		
    }

    function reenableRun($plugin_name = null)
    {
        $this->disableRun($plugin_name);
        $this->enableRun($plugin_name);
    }

    function enableRun($plugin_name = null)
    {
	if ($plugin_name === null)
	    $plugin_name = param("plugin_name");

        message(sprintf(_("Enabling plugin %s"), $plugin_name));

        if (!preg_match('/^[a-z][a-z0-9]*$/i', $plugin_name)) {
            error(sprintf(_("Illegal package name: %s"), $plugin_name));
            return false;
        }

        if(!@stat("./plugins/". $plugin_name)) {
            error(sprintf(_("Plugin %s doesn't exists!"), $plugin_name));
            return false;
        }                

	$schema_up = "./plugins/$plugin_name/schema.sql";
	$schema_down = "./plugins/$plugin_name/schema_drop.sql";
	$db_ok = true;

	if(@stat($schema_up)) {
	    foreach( explode(';', file_get_contents($schema_up)) as $sql) {
		$db_ok &= db::query($sql);
		if(!$db_ok) {
		    break;
		}
	    }
	}

	if (!$db_ok) {
	    error(_("Could not set up database tables"));
	}
	else {

	    $info = json_decode(file_get_contents("./plugins/$plugin_name/install.json"));

	    if(!$info) {
		error(_("Could not parse install information"));
	    }
	    else {

		db::begin();

		$ok = db::query("
insert into ci_plugin 
(
        name,
        description,
        version,
        author
)
values
(
        :name, :description, :version, :author
)",
				array(":name"=>$plugin_name,
				      ":description"=>$info->description,
				      ":version"=>$info->version,
				      ":author"=>$info->author));
		foreach($info->events as $event) {
		    $ok &= db::query("insert into ci_event (event_name, class_name) values (:ev, :cl)",
				     array(":ev"=>$event, ":cl"=>"{$plugin_name}Plugin"));

		}
		if (!$ok) {
		    db::rollback();
		}
		else {
		    db::commit();
		    message(_("No errors encountered!"));
		    util::redirect(makeUrl(array('task'=>'view' )));
		    return true;
		}
	    }
	}

	if(@stat($schema_down)) {
	    foreach( explode(';', file_get_contents($schema_down)) as $sql) {
		db::query($sql);
	    }
	}
	return false;
            
    }


    function disableRun()
    {
        $plugin_name = param("plugin_name");

        if (!preg_match('/^[a-z][a-z0-9]*$/i', $plugin_name)) {
            error(sprintf(_("Illegal package name: %s"), $plugin_name));
            return false;
        }
        
        db::query("delete from ci_plugin where name=:name",
                  array(":name" => $plugin_name));
        
        db::query("delete from ci_event where class_name=:name",
                  array(":name" => $plugin_name."Plugin"));
        
        $schema_down = "./plugins/$plugin_name/schema_drop.sql";
        if(@stat($schema_down)) {
            foreach( explode(';', file_get_contents($schema_down)) as $sql) {
                db::query($sql);
            }
        }

	return true;
    }
	
    function uninstallRun()
    {
        $plugin_name = param("plugin");

	$this->disableRun();
        
        util::rmdir("./plugins/". $plugin_name);
        util::redirect(makeUrl(array('task'=>'view' )));        
    }
    	
    function installRun()
    {
        $plugin_name = explode( ".", $_FILES['package_file']['name']);
        $plugin_name = $plugin_name[0];

        if (!preg_match('/^[a-z][a-z0-9]*$/i', $plugin_name)) {
            error(sprintf(_("Illegal package name: %s"), $plugin_name));
            return;
        }
            
        message(sprintf(_("Installing plugin %s"), $plugin_name));

        if(@stat("./plugins/". $plugin_name)) {
            error(sprintf(_("Plugin %s already exists!"), $plugin_name));
            return;
        }
            
            
        if(!@mkdir("./plugins/". $plugin_name)) {
            error(_("Could not create new directory for plugin. You probably need to set up the file permissions so that the web server can write to the plugins directory."));
            return;
        }
            
        system("unzip >/dev/null -d plugins/$plugin_name " . escapeshellarg($_FILES['package_file']['tmp_name']), $status);
        if ($status !== 0) {
            error(_("Failed to extract package contents"));
        }
        else {
            if ($this->enableRun($plugin_name))
	       return;
        }
            
        util::rmdir("./plugins/". $plugin_name);
            
    }
        
}

?>