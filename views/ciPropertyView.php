<?php

class ciPropertyView
{
	
    function render($controller)
    {
        util::setTitle(_("Properties"));
        $content = "";
        
        $form = "
<div class='button_list'><button type='submit'>"._("Update")."</button></div>
<table class='striped'>
<tr>
<th>
". _("Name")."
</th><th>
"._("Value")."
</th></tr>
";
        $idx = 0;
        $property_list = array("core.baseUrl"=>_("Base URL (Only needed if using search engine friendly URLs)"),
                               "chart.maxDepth" => _("Maximum dependency depth"),
                               "core.dateFormat" => _("Date format"),
			       "core.dateTimeFormat" => _("Combined date and time format"),
			       "core.locale" => _("User interface language"),			       "chart.maxItems" => _("Maximum number of matches in List views for which to draw a chart"),
			       "pager.itemsPerPage" => _("Maximum number of items per page"),
                               'core.baseURL' => _('Base URL for site'));
        
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
        $form .= "<div class='button_list'><button type='submit'>"._("Update")."</button></div>";
		
        $content .= form::makeForm($form,array('task'=>'update','controller'=>'ciProperty'));

        $controller->show($content);
		
	}
	

}


?>