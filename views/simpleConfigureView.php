<?php

class simpleConfigureView
	extends View
{

    function render($controller)
    {
        $form = $this->getPropertiesTable($controller);
        
        $content .= form::makeForm($form,array('task'=>'update','plugin'=>param('plugin'), 'controller'=>param('controller')));
        
        $controller->show($content);
	
    }
    
    function getPropertiesTable($controller)
    {
        
		
        $form = "
<div class='button_list'><button>"._("Update")."</button></div>
<table class='striped'>
<tr>
<th>
"._("Name")."
</th><th>
"._("Value")."
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
            $form .= $this->generateFormElement($name, "value_" . $idx, $value);
            
            $form .= "</td></tr>";
            
            $idx++;
        }
        
        $form .= "</table>";
        $form .= "<div class='button_list'><button>"._("Update")."</button></div>";
        
        return $form;
        	
    }

    function generateFormElement($prop_name, $form_name, $value) 
    {
        return "<input name='".htmlEncode($form_name)."' value='".htmlEncode($value)."'/>";
    }

}

?>