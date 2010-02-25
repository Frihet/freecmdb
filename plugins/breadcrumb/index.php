<?php

class breadcrumbPlugin
	extends Plugin
{

    function shutdownHandler($param)
    {
	$root = Property::get("plugin.breadcrumb.root");
	if (is_a($param['source'], "adminController"))
	    $root = Property::get("plugin.breadcrumb.admin_root");

	$breadcrumb = array_merge(array($root),
				  $param['source']->getContent("breadcrumb"));

	$breadcrumb = implode(" &gt; ", $breadcrumb);
	$breadcrumb = "<div class='breadcrumb'><div class='inner_breadcrumb'>{$breadcrumb}</div></div>";
        $param['source']->addContent('main_menu_post', $breadcrumb);
    }
    
}

?>