<?php


class CITypeEditor
extends AdminEditor
{

    function createWrite()
    {
        $name = param('name');
        $shape = param('shape','box');

        if (ciType::getId($name) !== null) {
            error("Another CI type named $name already exists");
            $this->viewWrite();
        }
        else {
            db::query("insert into ci_type (name, shape) values (:name, :shape)",
                      array(':name'=>$name, ':shape'=>$shape));
            if (db::count()) {
                message("CI type created");
            }
            else {
                error("CI type could not be created.");
            }
            
            redirect(makeUrl(array('action'=>'ci_type', 'task'=>null,'name'=>null,'shape'=>null)));
        }
    }

    function updateWrite()
    {
        $name = param('name');
        $shape = param('shape','box');
        $id = param('id');

        if (ciType::getId($name) !== null && ciType::getId($name) != $id) {
            error("Another CI type named $name already exists");
            $this->viewWrite();
        }
        else {
            db::query("update ci_type set name=:name, shape=:shape where id=:id",
                      array(':name' => $name, ':shape' => $shape, ':id' => $id));
            if (db::count()) {
                message("CI type updated");
            }
            else {
                error("CI type could not be found, not updated.");
            }
            
            redirect(makeUrl(array('action'=>'ci_type', 'task'=>null,'name'=>null,'shape'=>null)));
        }
    }

    function removeWrite()
    {
        $id = param('id');
        db::query('update ci_type set deleted=true where id=:id', array(':id'=>$id));

        if (db::count()) {
            message("CI type removed");
        }
        else {
            error("CI type could not be found, not removed.");
        }
        
        redirect(makeUrl(array('action'=>'ci_type', 'task'=>null,'id'=>null)));
    }
    

    function viewWrite() 
    {
        $ci_type_list = ciType::getTypes();	
        setTitle("CI types");
        $content = "<h1>CI types</h1>";
		
        $content .= "
<table class='striped'>";
        $content .= "<tr>";
        $content .= "<th>";
        $content .= "Name";
        $content .= "</th><th>";
        $content .= "Graph shape";
        $content .= "</th><th>";
            
        $content .= "</th></tr>";

        foreach($ci_type_list as $type_id => $type) {
                
            $content .= "<form accept-charset='utf-8' method='post' action='index.php'>";
                
            $content .= "<tr>";
            $content .= "<td>";
		
            $content .= "<input type='hidden' name='action' value='ci_type'/>";
            $content .= "<input type='hidden' name='task'   value='update'/>";
            $content .= "<input type='hidden' name='id'     value='$type_id'/>";
                
            $content .= "<input name='name'  size='16' length='64' value='".htmlEncode($type)."'/>";
		
            $content .= "</td><td>";

            $shape_select = form::makeSelect('shape', ciType::getShapes(), ciType::getShape($type_id));
            
            $content .= $shape_select;
		
            $content .= "</td><td>";

            $content .= "<button>Update</button>";
                
            $content .= makeLink(array('action' => 'ci_type', 'id' => $type_id,'task'=>'remove'),'Remove', 'remove', "Remove the CI " . $type);
            $content .= "</td></tr>";
            $content .= "</form>";
                
		
        }
            
        $name = htmlEncode(param('name',''));
            
        $shape_select = form::makeSelect('shape', ciType::getShapes());
            
        $content .= "
<tr>
  <form accept-charset='utf-8' method='post' action='index.php'>
    <td>
      <input type='hidden' name='action' value='ci_type'/>
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