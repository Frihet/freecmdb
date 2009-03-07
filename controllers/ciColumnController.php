<?php

require_once("controllers/adminController.php");


define('CI_COLUMN_TEXT', 0);
define('CI_COLUMN_TEXT_FORMATED', 1);
define('CI_COLUMN_LIST', 2);
define('CI_COLUMN_LINK_LIST', 3);
define('CI_COLUMN_IFRAME', 4);

class CiColumnController
extends AdminController
{

    function createWrite()
    {
        $name = param('name');
        $type = param('type',0);

        if (ciColumnType::getId($name) !== null) {
            error("Another CI type named $name already exists");
            $this->viewWrite();
        }
        else {
            db::query("insert into ci_column_type (name, type) values (:name, :type)",
                      array(':name'=>$name, ':type'=>$type));
            if (db::count()) {
                message("Column type created");
            }
            else {
                error("Column type could not be created.");
            }
            
            redirect(makeUrl(array('controller'=>'ciColumn')));
        }
    }

    function updateWrite()
    {
        $name = param('name');
        $type = param('type',0);
        $id = param('id');

        if (ciColumnType::getId($name) !== null && ciColumnType::getId($name) != $id) {
            error("Another column named $name already exists");
            $this->viewWrite();
        }
        else {
            db::query("update ci_column_type set name=:name, type=:type where id=:id",
                      array(':name' => $name, ':type' => $type, ':id' => $id));
            if (db::count()) {
                message("Column type updated");
            }
            else {
                error("Column type could not be found, not updated.");
            }
            
            redirect(makeUrl(array('controller'=>'ciColumn')));
        }
    }

    function removeWrite()
    {
        $id = param('id');
        db::query('update ci_column_type set deleted=true where id=:id', array(':id'=>$id));

        if (db::count()) {
            message("Column type removed");
        }
        else {
            error("Column type could not be found, not removed.");
        }
        
        
        redirect(makeUrl(array('controller'=>'ciColumn', 'task'=>'view', 'id'=>null)));
    }
    

    function viewWrite() 
    {
        $ci_column_list = ciColumnType::getColumns();
        util::setTitle("CI columns");
        $content = "<h1>CI columns</h1>";
        
	
        $form .= "
<table class='striped'>";
        $form .= "<tr>";
        $form .= "<th>";
        $form .= "Name";
        $form .= "</th><th>";
        $form .= "Type";
        $form .= "</th><th>";
            
        $form .= "</th></tr>";
        $idx = 0;
        
        foreach($ci_column_list as $column_id => $column) {
            
            $form .= "<tr>";
            $form .= "<td>";
		
            $form .= "<input type='hidden' name='id_$idx' value='$column_id'/>";
                
            $form .= "<input name='name_$idx'  size='16' length='64' value='".htmlEncode($column)."'/>";
		
            $form .= "</td><td>";
            
            $form .= form::makeSelect('type', ciColumnType::getTypes(), ciColumnType::getType($column_id));
                
            $form .= "</td><td>";
                
            $form .= makeLink(array('controller' => 'ciColumn', 'id' => $column_id,'task'=>'remove'),'Remove', 'remove', "Remove the CI " . $column, array('onclick'=>'return confirm("Are you sure?");'));
            $form .= "</td></tr>";
            
            $idx++;
        }
        
        $name = htmlEncode(param('name',''));
            
        $shape_select = form::makeSelect('type', ciColumnType::getTypes());
            
        $form .= "
<tr>
    <td>
      <input type='hidden' name='controller' value='ciColumn'/>
      <input type='hidden' name='task'   value='create'/>
      <input               name='name'   value='$name' size='16' length='64'/>
    </td>
    <td>
      $shape_select
    </td>
    <td>
    </td>
</tr>";
        

        $form .= "</table>";
        $form .= "<div class='button_list'><button>Update</button></div>";
        
        $content .= form::makeForm($form,array('task'=>'update','controller'=>'ciColumn'));
          
        
        $this->show($content);
            
		
    }
    
    
}


?>
