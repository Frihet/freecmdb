<?php

util::loadClass("simpleConfigureController");

class configureController
extends simpleConfigureController
{
    
    function getPropertyNames()
    {
        return array("plugin.drilldown.root" => _("Root node for drilldown"));
    }

}

?>