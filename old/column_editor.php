<?php

define('CI_COLUMN_TEXT', 0);
define('CI_COLUMN_TEXT_FORMATED', 1);
define('CI_COLUMN_LIST', 2);
define('CI_COLUMN_LINK_LIST', 3);
define('CI_COLUMN_IFRAME', 4);

class ColumnEditor
extends AdminEditor
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
            
            redirect(makeUrl(array('action'=>'ci_column')));
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
            
            redirect(makeUrl(array('action'=>'ci_column')));
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
        
        
        redirect(makeUrl(array('action'=>'ci_column', 'task'=>'view', 'id'=>null)));
    }
    

    function viewWrite() 
    {
        $ci_column_list = ciColumnType::getColumns();
        setTitle("CI columns");
        $content = "<h1>CI columns</h1>";
	
        $content .= "
<table class='striped'>";
        $content .= "<tr>";
        $content .= "<th>";
        $content .= "Name";
        $content .= "</th><th>";
        $content .= "Type";
        $content .= "</th><th>";
            
        $content .= "</th></tr>";

        foreach($ci_column_list as $column_id => $column) {
                
            $content .= "<form accept-charset='utf-8' method='post' action='index.php'>";
                
            $content .= "<tr>";
            $content .= "<td>";
		
            $content .= "<input type='hidden' name='action' value='ci_column'/>";
            $content .= "<input type='hidden' name='task'   value='update'/>";
            $content .= "<input type='hidden' name='id'     value='$column_id'/>";
                
            $content .= "<input name='name'  size='16' length='64' value='".htmlEncode($column)."'/>";
		
            $content .= "</td><td>";

            $shape_select = form::makeSelect('type', ciColumnType::getTypes(), ciColumnType::getType($column_id));
            
            $content .= $shape_select;
                
            $content .= "</td><td>";
                
            $content .= "<button>Update</button>";
                
            $content .= makeLink(array('action' => 'ci_column', 'id' => $column_id,'task'=>'remove'),'Remove', 'remove', "Remove the CI " . $column);
            $content .= "</td></tr>";
            $content .= "</form>";
                
		
        }
            
        $name = htmlEncode(param('name',''));
            
        $shape_select = form::makeSelect('type', ciColumnType::getTypes());
            
        $content .= "
<tr>
  <form accept-charset='utf-8' method='post' action='index.php'>
    <td>
      <input type='hidden' name='action' value='ci_column'/>
      <input type='hidden' name='task'   value='create'/>
      <input               name='name'   value='$name' size='16' length='64'/>
    </td>
    <td>
      $shape_select
    </td>
    <td>
      <button type='submit' class='add'>Add</button>
    </td>
  </form>
</tr>";
            
        $content .= "</table>";
  
        
        $this->show($content);
            
		
    }
    
    
}


?>