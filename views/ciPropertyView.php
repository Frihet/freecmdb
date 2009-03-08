<?php

class ciPropertyView
{
	
	function render($controller)
	{
		util::setTitle("Properties");
		$content = "";
		
        $form = "
<div class='button_list'><button>Update</button></div>
<table class='striped'>
<tr>
<th>
Name
</th><th>
Value
</th></tr>
";
        $idx = 0;
		$property_list = array("chart.max_depth" => "Maximum dependency depth");
		
        foreach($property_list as $name => $desc) {
            $value = Property::get($name);
			
            $form .= "<tr>";
            $form .= "<td>";
			
            $form .= "<input type='hidden' name='name_$idx' value='".htmlEncode($name)."'/>";
			$form .= htmlEncode($desc);
			
            $form .= "</td><td>";
			$form .= "<input name='value_$idx' value='".htmlEncode($value)."'/>";
			
            $form .= "</td></tr>";
            
            $idx++;
        }
        

        $form .= "</table>";
        $form .= "<div class='button_list'><button>Update</button></div>";
		
        $content .= form::makeForm($form,array('task'=>'update','controller'=>'ciProperty'));

        $controller->show($content);
		
	}
	

}


?>