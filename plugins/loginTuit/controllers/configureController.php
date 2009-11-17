<?php

util::loadClass("simpleConfigureController");

class configureController
extends simpleConfigureController
{
    
    function getPropertyNames()
    {
        return array('loginTuit.viewGroup'=>_('Group required for viewing CMDB'),
                     'loginTuit.editGroup'=>_('Group required for editing CMDB'),
                     'loginTuit.adminGroup'=>_('Group required for administrating CMDB'));
    }

}

?>