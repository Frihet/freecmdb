<?php

util::loadClass("simpleConfigureController");

class configureController
extends simpleConfigureController
{
    
    function getPropertyNames()
    {
        return array("plugin.breadcrumb.root" => _("Front page for this CMDB. Example: <a href='http://example.com/CMDB'>Home</a>"),
                     "plugin.breadcrumb.admin_root" => _("Front page for the admin page of this CMDB. Example: <a href='http://example.com/CMDB/admin'>CMDBAdministration</a>"));
    }

}

?>