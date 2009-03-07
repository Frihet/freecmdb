<?php

class ciView
extends View
{

    function render($controller) 
    {
        $ci = $controller->getCi();
        
        $edit = param("task", 'view')=='edit';
        $revision_id = param('revision_id');
        $revision = $revision_id !== null;

        $action_links = $controller->getActionMenu($edit);

        $action_str = $edit?"Editing":"Viewing";
        $action_str = $revision?"Viewing revision $revision_id of":$action_str;

        
        $content = "";
        
        if ($revision) {
            $content .="<em>This is an old revision of this item. ".makeLink(array('revision_id'=>null),'Click here to view the latest version').".</em>";
        }

        util::setTitle("$action_str " . $ci->getDescription());
        
        $content .= "<h1>".htmlEncode($action_str ." ". $ci->getDescription())."</h1>";
	
		
        if ($edit) {
            $content .= "<form accept-charset='utf-8' method='post' action='index.php'>";
            $content .= "<input type='hidden' name='controller' value='ci'>";
            $content .= "<input type='hidden' name='task' value='saveAll'>";
            $content .= "<input type='hidden' name='id' value='".$controller->id."'>";
        }
				
        $content .= "
<table class='striped ci_table'>";
				
        $content .= "<tr><th>Type</th><td>";
				
        $type_select = form::makeSelect('type', ciType::getTypes(), $ci->ci_type_id,'type_select');
				
        
        if ($edit) {
            $content .= $type_select;
        }
        else {
            $content .= $ci->type_name;
            $content .= "</td><td>";
            if (!$revision) {
                static $type_popup_form_id=0;
                $type_popup_id = "type_popup_form_$type_popup_form_id";
                $type_popup_form_id++;
                
                $form = $controller->makePopupForm('type',$type_select, 'type_select', $type_popup_id);            
                $content .= makePopup("Change CI type", "Edit", $form, 'edit', 'Change type of this ci', $type_popup_id);
            }
            
        }
        
        $content .= "</td></tr>\n";
        
        foreach($ci->_ci_column as $key => $value) {

            if (ciColumnType::getType($key) == CI_COLUMN_IFRAME && !$edit && !$revision) 
                {
                    continue;
                }
						

            $content .= "<tr>";
            $content .= "<th>";
            if ($edit) {
                $content .= "<label for='value_$key'>";
                $content .= ciColumnType::getName($key);
                $content .= "</label>";
                
            }
            else {
                $content .= ciColumnType::getName($key);
            }
            $content .= "</th><td>";
            if ($edit) {
                $content .= form::makeInput("value_$key", $value, $key, false, "value_$key");
            }
            else {
								
                if (!$value) {
                    $content .= "&lt;empty&gt;";
                }
                else {
                    $content .= form::makeInput('value', $value, $key, true, "display_$key");
                }
                
                $content .= "</td><td>";
                
                if (!$revision) {
                    static $column_popup_form_id=0;
                    $column_popup_id = "column_popup_form_$column_popup_form_id";
                    $column_popup_form_id++;
										
                    $form = $controller->makePopupForm($key,  form::makeInput('value', $value, $key, false, "value_$key"), "value_$key", $column_popup_id);
                    $content .= makePopup("Edit " . ciColumnType::getName($key), "Edit", $form, 'edit', 'Edit this CI field', $column_popup_id);
                }
            }
            
            $content .= "</td></tr>";
        }
        
        if (!$edit) {
            
            $content .= $this->makeDependencies($controller);
						
        }
        
        $content .= "</table>\n";
    
        if ($edit) {
            $content .= "
<div class='button_list'>
<button>Save</button>
</form>
</div>
";
            
        }

        if (!$edit && !$revision) {
            $content .= $this->makeIframes($controller);
						
        }
		
        if (!$edit) {
            $content .= $this->makeFigures($controller);
        }
        
        $controller->show($action_links,
                    $content);
    }

    /**
     * Print iframes.
     */
		
    function makeIframes($controller) 
    {
				
        $ci = $controller->getCi();
        $content = "";
				
        foreach($ci->_ci_column as $key => $value) {
								
            if (ciColumnType::getType($key) == CI_COLUMN_IFRAME) 
                {
                    $content .= "<div class='iframe_label'>\n";
								
                    $content .= "<span class='iframe_header'>".ciColumnType::getName($key)."</span>";
								
                    static $iframe_popup_form_id=0;
                    $iframe_popup_id = "iframe_popup_form_$iframe_popup_form_id";
                    $iframe_popup_form_id++;
								
                    $form = $controller->makePopupForm($key,  form::makeInput('value', $value, $key, false, "value_$key"), "value_$key", $iframe_popup_id);
                    $content .= makePopup("Edit " . ciColumnType::getName($key), "Edit", $form, 'edit', 'Edit this CI field', $iframe_popup_id);
								
                    $content .= "</div>\n";
                    $content .= form::makeInput('value', $value, $key, true, "iframe_$key");
								
								
                }
        }
        return $content;
				
    }
		

    /** 
     * Print all dependencies and dependants, including indirect
     * ones.
     *
     * This code should be refactored, it does almost exactly the
     * same thing twice, but with no code reuse.
     */
    function makeDependencies($controller)
    {
				
        $revision_id = param('revision_id');
        $revision = $revision_id !== null;
        $ci = $controller->getCi();
        $content= "";

        $my_name = htmlEncode($ci->getDescription(true));

        $all_ci_list = ci::fetch();
              
        $content .= "<tr><th colspan='3'>Depends on</th></tr>\n";
				
        foreach($ci->getDependencies() as $item) {
            $content .= "<tr>\n<td></td>\n<td>\n";
            $other_name = htmlEncode($item->getDescription());
            $content .= makeLink(array('id'=>$item->id), $item->getDescription(), null, "View all information on $other_name");
            $content .= "</td><td>\n";
            if (!$revision && $ci->isDirectDependency($item->id)) {
                $content .= makeLink(array('task'=>'removeDependency', 'dependency_id'=>$item->id), "Remove",'remove', "Remove dependency from $my_name to $other_name", array('onclick'=>'return confirm("Are you sure?");'));
            }
            $content .= "</td>\n</tr>\n";
        }	
				
        if (!$revision) {
            $content .= "<tr><td></td><td>";
						
            $form = "<select name='dependency_id'>\n";
            foreach($all_ci_list as $item) {
                $item_id = $item->id;
                if ($ci->isDirectDependency($item_id) || $ci->id == $item_id) {
                    continue;
                }
								
                $item_name = htmlEncode($item->getDescription());
                $form .= "<option value='$item_id'>$item_name</option>\n";
            }
						
            $form .= "</select></td><td><button type='submit'>Add</button>\n";
            $content .= form::makeForm($form, array('controller'=>'ci', 'task'=>'addDependency','id'=>$controller->id));
            $content .= "</td></tr>";
        }
				
        $content .= "<tr><th colspan='3'>Depended on by</th></tr>";
        $dependants = $ci->getDependants();
				
        foreach($dependants as $item) {
            $content .= "<tr>\n<td></td>\n<td>\n";
            $other_name = htmlEncode($item->getDescription());
            $content .= makeLink(array('id'=>$item->id), $item->getDescription(), null, "View all information on $other_name");
            $content .= "\n</td><td>\n";
            if (!$revision && $ci->isDirectDependant($item->id)) {
                $content .= makeLink(array('task'=>'removeDependant', 'dependant_id'=>$item->id), "Remove",'remove', "Remove dependency from $other_name to $my_name",array('onclick'=>'return confirm("Are you sure?");'));
            }
            $content .= "\n</td></tr>\n";
        }
				
        if (!$revision) {
						
            $content .= "<tr><td></td><td>";
						
            $form = "<select name='dependant_id'>\n";
						
            foreach($all_ci_list as $item) {
                $item_id = $item->id;
                if ($ci->isDirectDependant($item_id) || $ci->id == $item_id) {
                    continue;
                }
								
                $item_name = htmlEncode($item->getDescription());
                $form .= "<option value='$item_id'>$item_name</option>\n";
            }
            $form .= "</select></td><td><button type='submit'>Add</button>\n";
            $content .= form::makeForm($form, array('controller'=>'ci', 'task'=>'addDependant','id'=>$controller->id));
						
						
            $content .= "</td></tr>";
        }
        return $content;
						
    }
		
    /**
     * Print links to appropriate figures.
     */
		
    function makeFigures($controller) 
    {
        $revision_id = param('revision_id');
        $revision = $revision_id !== null;
        $ci = $controller->getCi();
        $content= "";
				
				
        $need_legend = false;
        $revision_str = $revision?"&revision_id=$revision_id":"";

        if (count($ci->getDependencies())) {
            $need_legend = true;
            $content .= $this->makeChart("chart.php?id={$ci->id}$revision_str", "Dependencies for this CI");

        }
            
        if (count($ci->getDependants())) {
            $need_legend = true;
            $content .= $this->makeChart("chart.php?id={$ci->id}&mode=dependants$revision_str", "Other items that depend on this CI");
								
        }
            
        if ($need_legend) {
            $content .= $this->makeChart('chart.php?legend=true', "Legend for the above figure(s)");
								
        }
						
		
        return $content;
    }
		
    function makeChart($param, $caption) 
    {
        return "
<div class='figure'>
<object data='$param' type='image/svg+xml'>
<img src='$param&format=png'/>
</object>
<span class='caption'>".htmlEncode($caption)."</span>
</div>";
				
    }
		


}


?>