<?php

class userGroupAdminView
	extends View
{

    function render($controller)
    {

        util::setTitle("User Group Administration");
        $content = "";
        
        $form = "
<div class='button_list'><button>Update</button></div>
<h3>Users</h3>
<table class='striped'>
<tr>
<th>
Group name
</th><th>
</th></tr>
";
        $idx = 0;
        $user_list = ciUserGroup::findAll() + array(null => new ciUser());
        $hidden = array('task'=>'update','controller'=>'userAdmin');
        //print_r($user_list);
        
        foreach($user_list as $user) {
            $remove = "";
            
            if($user->id !== null) {
                $remove = form::makeButton("Remove", "user[$idx][remove]", 1);
                                
                $hidden["user[$idx][id]"]= $user->id;
            }
            
            $form .= "<tr>";
            $form .= "<td>";
            $form .= form::makeText("user[$idx][username]",$user->name);
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