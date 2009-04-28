<?php

class ciDependencyview
{
	
	function render($controller)
	{
		

        $ci_dependency_list = ciDependencyType::getDependencies();
        util::setTitle("CI dependencies");
		$content = "";
		

        $form = "
<div class='button_list'><button>Update</button></div>
<table class='striped'>
<tr>
<th>
Name of dependency
</th><th>
Name of reverse dependency
</th><th>
Color
</th><th>
</th><th>
</th></tr>
";
        $idx = 0;
		
        foreach($ci_dependency_list as $dep) {
            
            $form .= "<tr>";
            $form .= "<td>";
			
            $form .= "<input type='hidden' name='id_$idx' value='".$dep->id."'/>";
            
            $form .= "<input name='name_$idx'  size='16' length='64' value='".htmlEncode(param("name_$idx",$dep->name))."'/>";
            $form .= "</td><td>";


            $form .= "<input name='reverse_name_$idx'  size='16' length='64' value='".htmlEncode(param("reverse_name_$idx",$dep->reverse_name))."'/>";
            $form .= "</td><td>";

            $shape_select = form::makeSelect("color_$idx", ciDependencyType::getColors(), param("color_$idx",ciDependencyType::getColor($dep->id)));
	    $form .=  $shape_select;
            $form .= "</td><td>";
	    
	    			
	    $checked = param("default",Property::get("ciDependency.default")) == $dep->id?"checked":"";
            $form .= "<input type='radio' name='default' value='{$dep->id}' id='default_$idx' $checked><label for='default_$idx'>Default</label>";
            $form .= "</td><td>";
            
            $form .= makeLink(array('controller' => 'ciDependency', 'id' => $dep->id,'task'=>'remove'),'Remove', 'remove', "Remove the dependency " . $dep->name, array('onclick'=>'return confirm("Are you sure?");'));
            $form .= "</td></tr>";
            
            $idx++;
        }
        
        $name = htmlEncode(param('name_new',''));
        $reverse_name = htmlEncode(param('reverse_name_new',''));
		
	$checked = param("default") == "new"?"checked":"";
	$shape_select = form::makeSelect("color_new", ciDependencyType::getColors(), param("color_$idx","black"));
        
        $form .= "
<tr>
    <td>
      <input               name='name_new'   value='$name' size='16' length='64'/>
    </td><td>
      <input               name='reverse_name_new'   value='$reverse_name' size='16' length='64'/>
    </td><td>
      $shape_select
    </td><td>
      <input type='radio' name='default' value='new' id='default_new' $checked><label for='default_new'>Default</label>
    </td>
    <td>
    </td>
</tr>";
        

        $form .= "</table>";
        $form .= "<div class='button_list'><button>Update</button></div>";
		
        $content .= form::makeForm($form,array('task'=>'update','controller'=>'ciDependency'));
        
		
        $controller->show($content);
		
    }
}

?>