<?php

util::loadClass("simpleConfigureController");

class configureController
extends simpleConfigureController
{
    
    function getPropertyNames()
    {
        return array("plugin.tuit.DSN" => "DSN for TUIT database");
    }

}

?>