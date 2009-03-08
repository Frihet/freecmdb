<?php

/**
 The help page
 */
class helpController
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
    

    function viewRun()
    {
		util::setTitle("FreeCMDB help");
		
            $this->add("Introduction", "
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


?>