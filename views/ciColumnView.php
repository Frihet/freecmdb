<?php

class ciColumnview
{
    
    function render($controller)
    {

        util::setTitle(_("CI columns"));
        $content = "";
	
        $hidden = array('task'=>'update','controller'=>'ciColumn');
            
            
        $form = "
<div class='button_list'><button type='submit'>"._("Update")."</button></div>
<table class='striped'>
<tr>
<th>
".("Name")."
</th><th>
".("Type")."
</th><th>
".("CI")."
</th><th>
".("Pattern")."
</th><th>
".("Prefix")."
</th><th>
".("Suffix")."
</th><th>
</th><th>
</th></tr>
";
        $idx = 0;
        $o = new ciColumnType();
        
        $ci_column_list = $o->findAll();
        $all_columns = param("column", array());
            
        foreach($ci_column_list + array(null => null) as $column) {
            
            $old = $all_columns[$idx];
            
            $form .= "<tr>";
            $form .= "<td>";
                
            if($column !== null)
                $hidden["column[$idx][id]"] = $column->id;
                
            $form .= "<input name='column[$idx][name]'  size='12' length='64' value='".htmlEncode(coalesce($old[name],$column->name))."'/>";
            $form .= "</td><td>";
            $form .= form::makeSelect("column[$idx][type]", 
                                      ciColumnType::getTypes(), 
                                      coalesce($old['type'],
                                               $column->type));
                
            $form .= "</td><td>";
            $form .= form::makeSelect("column[$idx][ci_type_id]",
                                      array(""=>"All")+ciType::getTypes(), 
                                      coalesce($old['ci_type_id'],
                                               $column->ci_type_id));
                
            //message("lalala " . $old['_default']. ", " .Property::get("ciColumn.default"));
            
            $checked = coalesce($old['_default'],Property::get("ciColumn.default")) == $column->id?"checked":"";
            
            $form .= "</td><td>";
            $form .= form::makeText("column[$idx][pattern]",coalesce($old['pattern'],
                                                              $column->pattern));


            $form .= "</td><td>";
            $form .= form::makeText("column[$idx][prefix]",coalesce($old['prefix'],
                                                              $column->prefix));


            $form .= "</td><td>";
            $form .= form::makeText("column[$idx][suffix]",coalesce($old['suffix'],
                                                              $column->suffix));


            $form .= "</td><td>";
            $form .= "<input type='radio' name='column[$idx][_default]' value='1' id='default_$idx' $checked><label for='default_$idx'>Default</label>";
            $form .= "</td><td>";
                
            if($column !== null)
                $form .= makeLink(array('controller' => 'ciColumn', 'id' => $column->id,'task'=>'remove'),_('Remove'), 'remove', _("Remove the column"), array('onclick'=>'return confirm("'.addcslashes(_("Are you sure?"),'"\\').'");'));
            
            $form .= "</td></tr>";
            
            $idx++;
        }
        
        $form .= "</table>";
        $form .= "<div class='button_list'><button type='submit'>"._("Update")."</button></div>";
        
        $content .= form::makeForm($form,$hidden);
                    
        $controller->show($content);
            
    }
}

?>