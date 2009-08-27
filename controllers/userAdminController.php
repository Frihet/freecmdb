<?php

require_once("controllers/adminController.php");


class userAdminController
extends adminController
{

    function viewRun()
    {
        $this->render("userAdmin");        
    }

    function updateRun()
    {
        $ok = true;
        db::begin();
        
        foreach(param('user') as $userArr){
            $user = new ciUser($userArr);
            if($user->username != "") {
                $ok &= $user->save();
            }
        }
        
        if($ok) {
            db::commit();
            message("Users saved");
            util::redirect(makeUrl(array('task'=>null)));
        }
        else {
            db::rollback();
            error("Errors while saving users");
            $this->render("userAdmin");        
        }
    }

}

?>