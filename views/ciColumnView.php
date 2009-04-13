<?php

class ciColumnview
{
	
	function render($controller)
	{
		

        $ci_column_list = ciColumnType::getColumns();
        util::setTitle("CI columns");
		$content = "";
		

        $form = "
<div class='button_list'><button>Update</button></div>
<table class='striped'>
<tr>
<th>
Name
</th><th>
Type
</th><th>
</th><th>
</th></tr>
";
        $idx = 0;
		
        foreach($ci_column_list as $column_id => $column) {
            
            $form .= "<tr>";
            $form .= "<td>";
			
            $form .= "<input type='hidden' name='id_$idx' value='$column_id'/>";
            
            $form .= "<input name='name_$idx'  size='16' length='64' value='".htmlEncode(param("name_$idx",$column))."'/>";
            $form .= "</td><td>";
            $form .= form::makeSelect("type_$idx", ciColumnType::getTypes(), param("type_$idx", ciColumnType::getType($column_id)));

            $form .= "</td><td>";
            $form .= form::makeSelect("ci_type_$idx", array(""=>"All")+ciType::getTypes(), param("type_$idx", ciColumnType::getCiType($column_id)));

	    $checked = param("default",Property::get("ciColumn.default")) == $column_id?"checked":"";
			            ciColumnType::$type_lookup[$row['id']] = $row['type'];

            $form .= "</td><td>";
            $form .= "<input type='radio' name='default' value='$column_id' id='default_$idx' $checked><label for='default_$idx'>Default</label>";
            $form .= "</td><td>";
            
            $form .= makeLink(array('controller' => 'ciColumn', 'id' => $column_id,'task'=>'remove'),'Remove', 'remove', "Remove the CI " . $column, array('onclick'=>'return confirm("Are you sure?");'));
            $form .= "</td></tr>";
            
            $idx++;
        }
        
        $name = htmlEncode(param('name_new',''));
		
        $shape_select = form::makeSelect('type_new', ciColumnType::getTypes(), param('type_new'));
	$ci_type_select = form::makeSelect("ci_type_new", array(""=>"All")+ciType::getTypes(), param("type_$idx", null));

	$checked = param("default") == "new"?"checked":"";
        
        $form .= "
<tr>
    <td>
      <input               name='name_new'   value='$name' size='16' length='64'/>
    </td>
    <td>
      $shape_select
    </td><td>
      $ci_type_select
    </td><td>
      <input type='radio' name='default' value='new' id='default_new' $checked><label for='default_new'>Default</label>
    </td>
    <td>
    </td>
</tr>";
        

        $form .= "</table>";
        $form .= "<div class='button_list'><button>Update</button></div>";
		
        $content .= form::makeForm($form,array('task'=>'update','controller'=>'ciColumn'));
        
		
        $controller->show($content);
		
    }
}

?>