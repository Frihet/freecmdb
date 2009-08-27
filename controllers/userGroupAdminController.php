<?php

require_once("controllers/adminController.php");


class userGroupAdminController
extends adminController
{

    function viewRun()
    {
        $this->render("userGroupAdmin");        
    }

    function updateRun()
    {
        $ok = true;
        db::begin();
        
        foreach(param('user_group') as $userArr){
            $user = new ciUserGroup($userArr);
            if($user->name != "") {
                $ok &= $user->save();
            }
        }
        
        if($ok) {
            db::commit();
            message("User groups saved");
            util::redirect(makeUrl(array('task'=>null)));
        }
        else {
            db::rollback();
            error("Errors while saving user groups");
            $this->render("userGroupAdmin");        
        }
    }

}

?>