<?php

class userAdminView
	extends View
{

    function render($controller)
    {

        util::setTitle("User Administration");
        $content = "";
        
        $form = "
<div class='button_list'><button>Update</button></div>
<h3>Users</h3>
<table class='striped'>
<tr>
<th>
Username
</th><th>
Full name
</th><th>
Email
</th><th>
</th></tr>
";
        $idx = 0;
        $user_list = ciUser::findAll() + array(null => new ciUser());
        $hidden = array('task'=>'update','controller'=>'userAdmin');
        //print_r($user_list);
        
        foreach($user_list as $user) {
            $remove = "";
            
            if($user->id !== null) {
                if($user != ciUser::$_me) {
                    $remove = form::makeButton("Remove", "user[$idx][remove]", 1);
                }
                
                $hidden["user[$idx][id]"]= $user->id;
            }
            
            $form .= "<tr>";
            $form .= "<td>";
            $form .= form::makeText("user[$idx][username]",$user->username);
            $form .= "</td>";
            $form .= "<td>";
            $form .= form::makeText("user[$idx][fullname]",$user->fullname);
            $form .= "</td>";
            $form .= "<td>";
            $form .= form::makeText("user[$idx][email]",$user->email);
            $form .= "</td>";
            
            $form .="</tr>";
            
            $idx++;
        }
        
        $form .= "</table>";
        $form .= "<div class='button_list'><button>Update</button></div>";
        
        $content .= form::makeForm($form,$hidden);
        $controller->show($content);
        
    }

}

?>