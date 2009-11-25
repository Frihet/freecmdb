<?php

util::loadClass("simpleConfigureController");

class configureController
extends simpleConfigureController
{
    
    function getPropertyNames()
    {
        return array("plugin.tuit.DSN" => _("DSN for TUIT database"),
                     "plugin.tuit.closedId" => _("Status id for closed issues"));
    }

}

?>