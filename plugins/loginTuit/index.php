<?php

class loginTuitPlugin
	extends Plugin
{

    function startupHandler($param)
    {
        $username = $_SERVER['REMOTE_USER'];	
        if($username) {
                ciUser::loginUser($username);
	}
    }
    
}

?>