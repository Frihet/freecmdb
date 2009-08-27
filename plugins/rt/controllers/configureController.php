<?php

util::loadClass("simpleConfigureController");

class configureController
extends simpleConfigureController
{
    
    function getPropertyNames()
    {
        return array("plugin.rt.DSN" => "DSN for RT database",
                     "plugin.rt.user" => "Username for RT user to use for creating tickets for CIs",
                     "plugin.rt.name" => "Rt installation name",
                     "plugin.rt.URL" => "URL to RT instance");
    }

}

?>