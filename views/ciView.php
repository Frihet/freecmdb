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
        
        $content = "";
	
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

            if (ciColumnType::getType($key) == CI_COLUMN_IFRAME && 
		!$edit && 
		!$revision) {
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

		$content .= implode("",$controller->getContent("ci_table"));

        
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

	foreach(CiDependencyType::getDependencies() as $dependency_type) 
	{
	    
	    $dep = "";
	    foreach($ci->getDependencies() as $item) {
		if($ci->getDependencyType($item)->id != $dependency_type->id) 
		{
		    continue;		    
		}
		$dep .= "<tr>\n<td></td>\n<td>\n";
		$other_name = htmlEncode($item->getDescription());
		$dep .= makeLink(array('id'=>$item->id), $item->getDescription(), null, "View all information on $other_name");
		$dep .= "</td><td>\n";
		if (!$revision && $ci->isDirectDependency($item->id)) {
		    $dep .= makeLink(array('task'=>'removeDependency', 'dependency_id'=>$item->id), "Remove",'remove', "Remove dependency from $my_name to $other_name", array('onclick'=>'return confirm("Are you sure?");'));
		}
		$dep .= "</td>\n</tr>\n";
	    }	
	    if( $dep != "") 
	    {
		$content .= "<tr><th colspan='3'>".htmlEncode($dependency_type->name)."</th></tr>\n";
		$content .= $dep;
	    }
	    
	    
	    $dependants = $ci->getDependants();

	    $dep2 = "";
			
	    foreach($dependants as $item) {
		if($item->getDependencyType($ci)->id != $dependency_type->id) 
		{
		    continue;		    
		}
		$dep2 .= "<tr>\n<td></td>\n<td>\n";
		$other_name = htmlEncode($item->getDescription());
		$dep2 .= makeLink(array('id'=>$item->id), $item->getDescription(), null, "View all information on $other_name");
		$dep2 .= "\n</td><td>\n";
		if (!$revision && $ci->isDirectDependant($item->id)) {
		    $dep2 .= makeLink(array('task'=>'removeDependant', 'dependant_id'=>$item->id), "Remove",'remove', "Remove dependency from $other_name to $my_name",array('onclick'=>'return confirm("Are you sure?");'));
		}
		$dep2 .= "\n</td></tr>\n";
	    }
	    
	    if($dep2) 
	    {
		if(!$dependency_type->isDirected() && !$dep)
		    $content .= "<tr><th colspan='3'>".htmlEncode($dependency_type->name)."</th></tr>";
		else if ($dependency_type->isDirected())
		    $content .= "<tr><th colspan='3'>".htmlEncode($dependency_type->reverse_name)."</th></tr>";
		$content .= $dep2;
		
	    }
	    
				
        }

        if (!$revision) {
	    $form = "<tr><td>".form::makeSelect('dependency_type_info',CiDependencyType::getDependencyOptions(),property::get(''))."</td><td>";
	    $arr = array();
            foreach($all_ci_list as $item) {
                $item_id = $item->id;
                /*
		if ($ci->isDirectDependant($item_id) || $ci->id == $item_id) {
                    continue;
		    }*/
		$arr[$item_id] = $item->getDescription();
            }
	    $form .= form::makeSelect('dependency_id', $arr, null);
	    
            $form .= "</td><td><button type='submit'>Add</button>\n";
            $form .= "</td></tr>";
            $content .= form::makeForm($form, array('controller'=>'ci', 'task'=>'addDependency','id'=>$controller->id));
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
            $content .= $this->makeChart($ci, false, $revision_id);
        }
            
        if (count($ci->getDependants())) {
            $need_legend = true;
            $content .= $this->makeChart($ci, true, $revision_id);
        }
            
        if ($need_legend) {
            //            $content .= $this->makeChart('chart.php?legend=true', "Legend for the above figure(s)");
        }
		
        return $content;
    }
		
    function makeChart($ci, $reverse, $revision_id) 
    {
        require_once("ciChart.php");
        $revision_str = $revision_id?"&revision_id=$revision_id":"";
        $caption = $is_dependencies?"Dependencies for this CI":"Other items that depend on this CI";
        $dep=$reverse?'&mode=dependants':'';
        $title = htmlEncode($caption);

        $url = "chart.php?id={$ci->id}{$dep}{$revision_str}";
        
        $c = new ciChart($ci, 
                         $reverse,
                         array(), 
                         null);
        
        $map_name = $c->getName();

        $res =  "
<div class='figure'>
<object data='$url' type='image/svg+xml' title='$title'>
<img src='$url&format=png' alt='$title' usemap='#$map_name'/>
</object>
<span class='caption'>".htmlEncode($caption)."</span>
</div>";
        
        $map = $c->render('cmapx');
        $res .= $map;
        return $res;
        
    }
		


}


?>