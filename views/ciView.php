<?php

class ciView
extends View
{

    function em($str)
    {
	return "<em>".$str."</em>";
    }
    

    function makeInput($ci_id, $name, $value, $column_id, $read_only=false, $id=null) 
    {
        $type = ciColumnType::get($column_id);

        $res = "";
        
        if($type->prefix != "")
            $res .= $type->prefix . "&nbsp;";
        
        if ($read_only) {
            if($value == null) {
                $res .= "&lt;empty&gt;";    
            }
            else {
                
            switch($type->type) {
		case CI_COLUMN_TEXT_FORMATED:
		    $res .= $value; /* We should plug in a filter to disallow weird html here, but it's not a top priority item. */
		    break;
                    
                case CI_COLUMN_FILE:
                    $res .= makeLink(makeUrl(array("controller"=>"download", "column_id"=>$column_id, "ci_id" => $ci_id)), $value);
                    break;
                    		    
		case CI_COLUMN_IFRAME:
		    if ($value == null || $value == '') 
		    {
			break;
		    }
		    
		    if ($id == null) 
		    {
			$id = "form_iframe_" . form::$iframe_id;
			form::$iframe_id++;
		    }
		    $res .= "
<iframe class='freecmdb_iframe' id='".htmlEncode($id)."' name='".htmlEncode($id)."' src='".htmlEncode($value)."'>"._("Iframes not supported by this browser.")."
</iframe>";
		    
		    break;
		    
		case CI_COLUMN_LIST:
		    if ($value != null) {
                        $res .= ciColumnList::getName($value);
		    }
                    else {
                        $res = htmlEncode(_("<invalid value>"));
		    }
		    break;
		    
		case CI_COLUMN_EMAIL:
		    $ev = htmlEncode($value);
		    $res .= "<a href='mailto:$ev'>$ev</a>";
		    break;
                    
		case CI_COLUMN_URI:
		    $ev = htmlEncode($value);
		    $res .= "<a href='$ev'>$ev</a>";
		    break;
                    
		default:
		    $res .= htmlEncode($value);
		    break;
            }
            }
            
        }
        else {
            
            $id_str = $id?'id="'.htmlEncode($id).'"':'';
            
            switch($type->type) {
            case CI_COLUMN_IFRAME:
            case CI_COLUMN_TEXT:
            case CI_COLUMN_EMAIL:
            case CI_COLUMN_URI:
                $res .= form::makeText($name, $value, $id);
                break;

            case CI_COLUMN_FILE:
                $res .= form::makeFile($name, $id);
                break;
                                
            case CI_COLUMN_DATE:
                $res .= form::makeText($name, $value, $id);
                $res .= '<script type="text/javascript">
$(function()
        {
                $("#'.htmlEncode($id).'").datePicker(
                        {
                                startDate: "1970-01-01"
                        }
                );
        }
);
</script>';
                break;
                
            case CI_COLUMN_TEXT_FORMATED:
                /*
                 Put our editor in a div with a hard coded width, and hard code the width here, not in the css. 
                */
                $res .= "\n<textarea style='height: 250pt; width: 400pt;' class='rich_edit' cols='64' rows='16' $id_str name='".htmlEncode($name)."'>".htmlEncode($value)."</textarea>";
                break;
            case CI_COLUMN_LIST:
                /*
                 We _need_ an id here to update the select in the ajax code. Create one if not provided.
                */
                if (!$id) {
                    static $temp_id=0;
                    $id = "temp_id_" . ($temp_id++);
                }
                
                $res .= form::makeSelect($name, ciColumnList::getItems($column_id), $value, $id);
                $res .= makePopup(_("Edit item list"), "More...", self::makeColumnListEditor($column_id, $id), "edit" );
                
                break;
                
            default:
                $res .= htmlEncode($value);
                break;
                
            }
        }

        if($type->suffix != "")
            $res .= "&nbsp;". $type->suffix;
        return $res;
    }


    function makeColumnListEditor($column_id, $select_id, $table_id=null) 
    {
        static $column_list_counter=0;

        if($table_id === null) {
            $table_id = 'column_list_input_' . ($column_list_counter++);
        }
        
        $res = "<table class='striped' id='$table_id'>
<tr>
<th>"._("Name")."</th>
<th></th>
</tr>
";
	
        foreach(ciColumnList::getItems($column_id) as $id => $name) {
            $item_id = $table_id . "_" . ($column_list_counter++);
            $remove = "
<button type='button' onclick='submitAndReloadColumnList(\"removeColumnListItem\",\"$column_id\", $id, \"$item_id\", \"$table_id\", \"$select_id\")'>Remove</button>
";
            $update = "
<button type='button' onclick='submitAndReloadColumnList(\"updateColumnListItem\",\"$column_id\", $id, \"$item_id\", \"$table_id\", \"$select_id\")'>Update</button>
";
            $res .= "<tr><td>".self::makeInput(null, $item_id, $name, null, false, $item_id). "</td><td>$update $remove</td></tr>";
        }

        $item_id = $table_id . "_" . ($column_list_counter++);
        
        $add = "
<button type='button' onclick='submitAndReloadColumnList(\"addColumnListItem\",\"$column_id\", null, \"$item_id\", \"$table_id\", \"$select_id\")'>Add</button>
";
        $res .= "<tr><td>".self::makeInput(null, $item_id, '', null, false, $item_id )."</td><td>" . $add . "</td></tr>";
        
        $res .= "</table>";
        return $res;
        
    }
    

    function render($controller) 
    {
        $ci = $controller->getCi();
        
        $edit = param("task", 'view')=='edit' ||
	    param("task", 'view')=='create';

	$create = (param("task", 'view')=='create') && 
	    (param("type")==null) ;

        if($edit) {
            ciUser::assert_edit();
        }

        $revision_id = param('revision_id');
        $revision = $revision_id !== null;
	if (!$revision) 
	{
	    $deleted = !!$ci->deleted;
	    
	}
	
        $is_readonly = $revision || $deleted || !ciUser::can_edit();
        $action_links = $controller->getActionMenu($edit);
        
	if ($revision) 
	{
	    $action_str = sprintf(_("Viewing revision %s of %s"),$revision_id, $ci->getDescription());
	}
	else 
	{
	    $action_str = sprintf($edit?_("Editing %s"):_("Viewing %s"), $ci->getDescription());    
	}
        
        $content = "";
        
        if ($revision) {
            $content .=$this->em( _("This is an old revision of this item.")." ".makeLink(array('revision_id'=>null),_('Click here to view the latest version'))).".";
        }
	else if ($deleted) 
	{
            $content .=$this->em(_("This item has been deleted"));
	}
	
        util::setTitle($action_str);

        $form = "";
        
        $form .= "
<table class='striped ci_table'>";
				
        $form .= "<tr><th>"._("Type")."</th><td>";
				
        $type_select = form::makeSelect('type', ciType::getTypes(), $ci->ci_type_id,'type_select');
				
        
        if ($edit) {
            $form .= $type_select;
        }
        else {
            $form .= $ci->type_name;
            $form .= "</td><td>";
            if (!$is_readonly) {
                static $type_popup_form_id=0;
                $type_popup_id = "type_popup_form_$type_popup_form_id";
                $type_popup_form_id++;
                
                $sub_form = $controller->makePopupForm('type',$type_select, 'type_select', $type_popup_id);            
                $form .= makePopup(_("Change CI type"), _("Edit"), $sub_form, 'edit', _('Change type of this CI'), $type_popup_id);
            }
            
        }
        
        $form .= "</td></tr>\n";
        if($ci->_ci_column) 
	{
	    
        foreach($ci->_ci_column as $key => $value) {

            if (ciColumnType::getType($key) == CI_COLUMN_IFRAME && 
		!$edit && 
		!$revision) {
		continue;
	    }

            $value = param("value_$key", $value);
            
            $form .= "<tr>";
            $form .= "<th>";
            if ($edit) {
                $form .= "<label for='value_$key'>";
                $form .= ciColumnType::getName($key);
                $form .= "</label>";
                
            }
            else {
                $form .= ciColumnType::getName($key);
            }
            $form .= "</th><td>";
            if ($edit) {
                $form .= self::makeInput($ci->id, "value_$key", $value, $key, false, "value_$key");
            }
            else {
								
                $form .= self::makeInput($ci->id, 'value', $value, $key, true, "display_$key");
                                
                $form .= "</td><td>";
                
                if (!$is_readonly) {
                    static $column_popup_form_id=0;
                    $column_popup_id = "column_popup_form_$column_popup_form_id";
                    $column_popup_form_id++;
										
                    $sub_form = $controller->makePopupForm($key,  self::makeInput($ci->id, 'value', $value, $key, false, "value_$key"), "value_$key", $column_popup_id);
                    $form .= makePopup(sprintf(_("Edit %s"), ciColumnType::getName($key)), _("Edit"), $sub_form, 'edit', _('Edit this CI field'), $column_popup_id);
                }
            }
            
            $form .= "</td></tr>";
        }
        }
	
        if (!$edit) {
            
            $form .= $this->makeDependencies($controller, $ci, $revision, $is_readonly);
            
        }
        //echo "lalala" . htmlEncode($form);
        
        $form .= implode("",$controller->getContent("ci_table"));
        
        $form .= "</table>\n";
    
        if ($edit) {
            $form .= "
<div class='button_list'>
<button type='submit'>"._("Save")."</button>
</div>
";
            $content .= form::makeForm($form, array('controller' =>'ci',
                                                    'task'=>$create?'create':'saveAll',
                                                    'id'=>$controller->id), 
                                       "post", true);
            
        }
        else {
            $content .= $form;
        }
        
        if (!$edit && !$is_readonly) {
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
								
                    $form = $controller->makePopupForm($key,  self::makeInput($ci->id, 'value', $value, $key, false, "value_$key"), "value_$key", $iframe_popup_id);
                    $content .= makePopup(sprintf(_("Edit %s"), ciColumnType::getName($key)), _("Edit"), $form, 'edit', _('Edit this CI field'), $iframe_popup_id);
								
                    $content .= "</div>\n";
                    $content .= self::makeInput($ci->id, 'value', $value, $key, true, "iframe_$key");
								
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
    function makeDependencies($controller, $ci, $revision, $is_readonly)
    {
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
		$dep .= makeLink(array('id'=>$item->id), $item->getDescription(), null, sprintf(_("View all information on %s"), $other_name));
		$dep .= "</td><td>\n";
		if (!$is_readonly && $ci->isDirectDependency($item->id)) {
		    $dep .= makeLink(array('task'=>'removeDependency', 'dependency_id'=>$item->id), _("Remove"),'remove', sprintf(_("Remove dependency from %s to %s"), $my_name, $other_name), array('onclick'=>'return confirm("' .addcslashes(_("Are you sure?"),'"\\').'");'));
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
		$dep2 .= makeLink(array('id'=>$item->id), $item->getDescription(), null, sprintf(_("View all information on %s"),$other_name));
		$dep2 .= "\n</td><td>\n";
		if (!$is_readonly && $ci->isDirectDependant($item->id)) {
		    $dep2 .= makeLink(array('task'=>'removeDependant', 'dependant_id'=>$item->id), _("Remove"),'remove', sprintf(_("Remove dependency from %s to %s"), $other_name, $my_name),array('onclick'=>'return confirm("'.addcslashes(_("Are you sure?"),'"\\').'");'));
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

	$dep_indirect = "";
	
	foreach($ci->getDependencies() as $item) {
	    if (!$ci->isDirectDependency($item->id)) {
		$dep_indirect .= "<tr>\n<td></td>\n<td>\n";
		$other_name = htmlEncode($item->getDescription());
		$dep_indirect .= makeLink(array('id'=>$item->id), $item->getDescription(), null, sprintf(_("View all information on %s"), $other_name));
		$dep_indirect .= "</td><td>\n";
		$dep_indirect .= "</td>\n</tr>\n";
	    }
	}	
	if( $dep_indirect != "") 
	{
	    $content .= "<tr><th colspan='3'>"._("Indirect dependencies")."</th></tr>\n";
	    $content .= $dep_indirect;
	}
	    



        if (!$is_readonly) {
	    $content .= "<tr><th colspan='3'>"._("Add new dependency")."</th></tr>\n";
	    $form = "<tr><td>".form::makeSelect('dependency_type_info', CiDependencyType::getDependencyOptions(),property::get(''))."</td><td>";
            $form .= $controller->getApplication()->makeCiSelector('dependency_id', null);
	    
            $form .= "</td><td><button type='submit'>"._("Add")."</button>\n";
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
        $ci = $controller->getCi();
        $content= "";
				
        $need_legend = false;

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
        $caption = !$is_dependencies?_("Dependencies for this CI"):_("Other items that depend on this CI");
        $dep=$reverse?'&mode=dependants':'';
        $title = htmlEncode($caption);

        $url = util::getPath()."chart.php?id={$ci->id}{$dep}{$revision_str}";
        
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