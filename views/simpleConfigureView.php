<?php

class simpleConfigureView
	extends View
{

    function render($controller)
    {
        $form = $this->getPropertiesTable($controller);
        
        $content .= form::makeForm($form,array('task'=>'update','plugin'=>'rt', 'controller'=>'configure'));
        
        $controller->show($content);
	
    }
    
    function getPropertiesTable($controller)
    {
        
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
        $property_list = $controller->getPropertyNames();
            
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
        
        return $form;
        	
    }

}

?>