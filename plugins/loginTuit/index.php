<?php

class loginTuitPlugin
	extends Plugin
{

    function startupHandler($param)
    {
        $session_id = $_COOKIE['sessionid'];
        
        $ch = curl_init();
        $host = $_SERVER['SERVER_ADDR'];
        
        curl_setopt($ch, CURLOPT_URL, "http://" .$host ."/tuit/account/session");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIE, "sessionid=$session_id");
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
//        message($_SERVER);
//	message($info);
	
        if ($res !== false) {
            $msg = json_decode($res);
            //message($msg);
	    
            if ($msg != null && strlen($msg->username)) {
                $vg = property::get('loginTuit.viewGroup');
                $eg = property::get('loginTuit.editGroup');
                $ag = property::get('loginTuit.adminGroup');
                $can_view=$can_edit=$can_admin=0;
                if ($vg == '' || in_array($vg, $msg->groups)) {
                    $can_view = 1;
                }
                if ($eg == '' || in_array($eg, $msg->groups)) {
                    $can_edit = 1;
                }
                if ($ag == '' || in_array($ag, $msg->groups)) {
                    $can_admin = 1;
                }

                //message("view: $can_view, edit: $can_edit, admin: $can_admin");

                ciUser::setUser($msg->username,$msg->first_name . " " . $msg->last_name, $msg->email, $can_view, $can_edit, $can_admin);
                $param['source']->addContent('action_menu_pre',sprintf("<li>"._("User").": <a href='/tuit/account/%s'>%s - %s</a></li>\n<li><a href='/tuit/account/logout'>"._("Log out")."</a></li>\n",
                                                                       ciUser::$_me->username,
                                                                       ciUser::$_me->username,
                                                                       ciUser::$_me->fullname));
                
                return;
            }
            
            /*            message("Status: " . $info['http_code']);
            message("Got back " . strlen($res) . " characters of information");
            message("Output from session query: " . $res);
            */
            
        }
        
        util::redirect("http://" .$host ."/tuit/account/login");

	//echo "WOO HOOO!";
    }
    
    function configure($controller)
    {
    }

}

?>