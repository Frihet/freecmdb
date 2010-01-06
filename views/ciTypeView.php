<?php

class ciTypeView
extends View
{

    function render($controller)
    {
        $ci_type_list = ciType::getTypes();	
        util::setTitle(_("CI types"));		            
                            

        $content .= "
<button type='submit'>"._("Update")."</button>
<table class='striped'>";
        $content .= "<tr>";
        $content .= "<th>";
        $content .= _("Name");
        $content .= "</th><th>";
        $content .= _("Graph shape");
        $content .= "</th><th>";
            
        $content .= "</th></tr>";
        $idx = 0;
        
        foreach($ci_type_list as $type_id => $type) {
                
            $content .= "<tr>";
            $content .= "<td>";
            
            $content .= "<input type='hidden' name='id_$idx'     value='$type_id'/>";
            
            $content .= "<input name='name_$idx'  size='16' length='64' value='".htmlEncode(param("name_$idx",$type))."'/>";
            
            $content .= "</td><td>";
            
            $shape_select = form::makeSelect("shape_$idx", ciType::getShapes(), param("shape_$idx",ciType::getShape($type_id)));
            
            $content .= $shape_select;
            
            $content .= "</td><td>";
            $content .= makeLink(array('controller' => 'ciType', 'id' => $type_id,'task'=>'remove'),'Remove', 'remove', "Remove the CI " . $type , array('onclick'=>'return confirm("Are you sure?");'));
            $content .= "</td></tr>";
            $idx++;
            
        }
            
        $name_new = htmlEncode(param('name_new',''));
            
        $shape_select = form::makeSelect('shape_new', ciType::getShapes(),param('shape_new'));
            
        $content .= "
<tr>
    <td>
      <input name='name_new'   value='$name_new' size='16' length='64'/>
    </td>
    <td>
      $shape_select
    </td>
    <td>
    </td>
</tr>";
            
        $content .= "</table>
<button>"._("Update")."</button>
";
        $content = form::makeForm($content, array('task'=>'update','controller'=>'ciType'));
                    
        $controller->show($content);
        
    }
    

}

?>