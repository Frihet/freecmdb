<?php
  /**
   Controller for ci objects
   */
class CIController
extends Controller
{
    var $id;
 
    function __construct() 
    {
        $this->id = param('id');
        
    }

    function addDependencyWrite()
    {
        $other_id = param('dependency_id');
        $ci = $this->getCi();
        $ci->addDependency($other_id);
        message("Dependency added");
        redirect(makeUrl(array('task'=>null, 'dependency_id'=>null)));
    }

    function addDependantWrite()
    {
        $other_id = param('dependant_id');
        $ci_list = ci::fetch(array('id_arr'=>array($other_id)));
        $ci = $ci_list[$other_id];
        $ci->addDependency($this->id);
        message("Dependant added");
        redirect(makeUrl(array('task'=>null, 'dependant_id'=>null)));
    }

    function removeDependencyWrite()
    {
        $other_id = param('dependency_id');
        $ci = $this->getCi();
        $ci->removeDependency($other_id);
        message("Dependency removed");
        redirect(makeUrl(array('task'=>null, 'dependency_id'=>null)));
    }

    function removeDependantWrite()
    {
        $other_id = param('dependant_id');
        $ci_list = ci::fetch(array('id_arr'=>array($other_id)));
        $ci = $ci_list[$other_id];
        $ci->removeDependency($this->id);
        message("Dependant removed");
        redirect(makeUrl(array('task'=>null, 'dependency_id'=>null)));
    }
    
    function updateField($key, $value)
    {
        if ($key=='type') {
            log::add($this->id, CI_ACTION_CHANGE_TYPE);
            db::query('update ci set ci_type_id=:type_id where id=:id',
                      array(':type_id'=>param('type'),':id'=>param('id')));
            return;
        }

        log::add($this->id, CI_ACTION_CHANGE_COLUMN, $key);
        
        $query = "
update ci_column
set value=:value
where ci_id=:id
and ci_column_type_id=:key
";
        $arr = array('key'=>$key, 'value'=>$value, 'id'=>$this->id);
        $res = db::query($query, $arr);
        $count = db::count();
        if (!$count) {
            $query = "
insert into ci_column
(
        ci_id,
        ci_column_type_id,
        value
)
values
(
        :id,
        :key,
        :value
)";
            $res = db::query($query, $arr);
            
        }
    
    }
    
    function updateFieldWrite()
    {
        $key = param('key');
        $value = param('value');
        
        
        $this->updateField($key, $value);
        

        message('CI updated');
        
        redirect(makeUrl(array('task'=>null, 'key'=>null, 'value' => null)));
    }
    
    function createWrite()
    {
        db::query("insert into ci (ci_type_id) select id from ci_type where deleted=false limit 1");
        $id = db::lastInsertId("ci_id_seq");
        
        log::add($id, CI_ACTION_CREATE);
        $this->id = $_GET['id'] = $id;

        message('CI created');
        redirect(makeUrl(array('task'=>'edit', 'key'=>null, 'value' => null, 'id' => $id)));
    }
    
    function getCi() 
    {
        if ($this->ci != null) {
            return $this->ci;
        }
        $ci_list = ci::fetch(array('id_arr'=>array($this->id)));
        $this->ci = $ci_list[$this->id];
        return $this->ci;
    }

    function saveAllWrite() 
    {
        $arr = array('controller'=>'ci', 'id'=>$this->id, 'task'=>null, 'dependency_id'=>null);
        foreach($_REQUEST as $key => $value) {
            $prefix = substr($key, 0, 6);
            $suffix = substr($key, 6);
            if ($prefix == 'value_') {
                $this->updateField($suffix, $value);
                $arr[$key] = null;
            }
        }
        $type_id = param('type');
        if ($type_id) {
            db::query('update ci set ci_type_id=:type_id where id=:id',
                      array(':type_id'=>$type_id,':id'=>param('id')));

        }
        message('Fields updated');
        redirect(makeUrl($arr));
    }
    
    function removeWrite()
    {
        log::add($this->id, CI_ACTION_REMOVE);
        $res = db::query("update ci set deleted=true where id = :id", array('id'=>$this->id));
        if ($res->rowCount()) {
            message('CI removed');
        }
        else {
            error('Could not remove CI, not found.');
        }
        
        redirect(makeUrl(array('id'=>null, 'controller'=>'ciList', 'task'=>null)));
    }
    
    function makePopupForm($field_name, $input, $input_id, $popup_id)
    {
        
        $form = "<p><label for='$input_id'>New value:</label></p><p>" . $input;
        $form .= "</p><p><button type='submit'>Save</button> ";
        $form .= "<button type='button' onclick='popupHide(\"$popup_id\")'>Cancel</button></p>";
        
        return makeForm($form, array('task'=>'updateField','controller'=>'ci','id'=>$this->id,'key'=>$field_name));
    }
    
    function getActionMenu() 
    {
        $action_links = array();
	
        $task = param('task','view');
                
        if($task!='view') {
            $action_links[] = makeLink(array('task'=>'view','revision_id'=>null), "View", 'view', 'Go back to read-only view of this CI');
        }
        if ($task != 'edit') {
            $action_links[] = makeLink(array('task'=>'edit','revision_id'=>null), "Edit", 'edit',  'Edit all fields of this CI');
        }
        if($task != 'history') {
            $action_links[] = makeLink(array('task'=>'history','revision_id'=>null), "History", null, 'Show the entire revision history for this item.');
        }
        
        $action_links[] = makeLink("?controller=ci&amp;task=create", "Create new item", null, "Creat an empty new CI");
        $action_links[] = makeLink(array('task'=>'remove','revision_id'=>null), "Remove", 'remove', 'Remove this CI');
        $action_links[] = makeLink(array('task'=>'copy','revision_id'=>null), "Copy", 'copy', 'Copy this CI');
        return $action_links;
        
    }
    
    function copyWrite()
    {
        $id_orig = param('id');
        db::query('insert into ci (ci_type_id) select ci_type_id from ci where id=:id', array(':id'=>$id_orig));
        $id_new = db::lastInsertId("ci_id_seq");

        db::query('
insert into ci_column (ci_id, ci_column_type_id, value)
select :new_id, ci_column_type_id, value from ci_column where ci_id = :old_id', array(':old_id'=>$id_orig, ':new_id' => $id_new));

        db::query('
insert into ci_dependency (ci_id, dependency_id)
select :new_id, dependency_id from ci_dependency where ci_id = :old_id', array(':old_id'=>$id_orig, ':new_id' => $id_new));

        db::query('
insert into ci_dependency (ci_id, dependency_id)
select ci_id, :new_id from ci_dependency where dependency_id = :old_id', array(':old_id'=>$id_orig, ':new_id' => $id_new));

        redirect(makeUrl(array('id'=>$id_new, 'task'=>null)));
			
        /*
         FIXME: Update default column with shiny new value, e.g. append (copy) to it or something...
        */
    }
	

    function editWrite()
    {
        $this->viewWrite();
    }

    function formatHistoryValue($val, $column_id) 
    {
        if ($val ==null) {
            return "<em class='value'>&lt;empty&gt;</em>";
        }
        
        $type = ciColumnType::getType($column_id);
        if ($type != CI_COLUMN_IFRAME) {
            $val = form::makeInput(null, $val, $column_id, true);
            $val = strip_tags($val);
        }        

        
        if(strlen($val) > 64) {
            $val = substr($val, 0, 60) . "...";
        }
        
        return "<em class='value'>«".htmlEncode($val)."»</em>";

    }

    function revertWrite()
    {
        $ci = $this->getCi();
        $revision_id = param('revision_id');
        
        $edits = db::fetchList('
select cl2.ci_id, cl2.action, cl2.type_id_old, cl2.column_id, cl2.column_value_old, cl2.dependency_id
from ci_log as cl
join ci_log as cl2
on cl2.create_time >= cl.create_time 
where cl.id=:revision_id and (cl2.ci_id = :ci_id or cl2.dependency_id = :ci_id) 
order by cl2.create_time desc;', 
                               array(':ci_id'=>$this->id, ':revision_id'=>$revision_id));
        
        $ci_orig = clone($ci);

        foreach($edits as $edit) {
            echo "WOWOWO<br>";
            
            $ci->apply($edit);
        }
        if ($ci->ci_type_id != $ci_orig->ci_type_id) {
            $this->updateField('type', $ci->ci_type_id);
            echo "LALALA col type {$ci->ci_type_id}<br>" ;
            
        }
        foreach($ci->_ci_column as $key=>$value) {
            if ($value !== $ci_orig->_ci_column[$key]) {
                $this->updateField($key, $value);
                echo "LALALA col $key $value<br>" ;
                
            }
        }
        
        //redirect(makeUrl(array('task'=>'view', 'revision_id'=>null)));
    }
    

    
    
    function historyWrite()
    {
        $ci = $this->getCi();
        $action_links = $this->getActionMenu();
        util::setTitle("History for " . $ci->getDescription());
        $content = "<h1>History for ".htmlEncode($ci->getDescription())."</h1>";

        $edits = db::fetchList('
select  ci_log.id, extract (epoch from create_time) as create_time, 
        ci_log.ci_id, action, 
        type_id_old, ci_log.column_id, 
        column_value_old, dependency_id, 
        cc1.value as dependency_name,
        cc2.value as dependant_name,
        ci_log.user_id,
        ci_user.username
from ci_log 
join ci_user
on ci_log.user_id = ci_user.id
left join ci_column cc1
on ci_log.dependency_id = cc1.ci_id and cc1.ci_column_type_id=6
left join ci_column cc2
on ci_log.ci_id = cc2.ci_id and cc2.ci_column_type_id=6
where ci_log.ci_id = :ci_id or ci_log.dependency_id = :ci_id
order by create_time desc', 
                               array(':ci_id'=>$this->id));
        $content .= "
<table class='striped history_table'>
<tr>
<th>
Description of change
</th>
<th>
Time
</th>
<th>
Changed by
</th>
<th>
</th>
</tr>
";
        
        foreach($edits as $edit) {
            /* Start out with non-specific description. If we have a
             more detailed one, we raplace it later.
            */
            $desc = htmlEncode(ciAction::getDescription($edit['action']));
            $noop = false;
            
            switch($edit['action']) {
            case CI_ACTION_ADD_DEPENDENCY:
                if ($edit['ci_id'] == $this->id) {
                    $desc = "Added dependency to ci ". $edit['dependency_name'];
                }
                else {
                    $desc = "Added dependency from ci ". $edit['dependant_name'];
                }
                
                break;
            case CI_ACTION_REMOVE_DEPENDENCY:
                if ($edit['ci_id'] == $this->id) {
                    $desc = "Removed dependency to ci ". $edit['dependency_name'];
                }
                else {
                    $desc = "Removed dependency from ci ". $edit['dependant_name'];
                }
                break;

            case CI_ACTION_CHANGE_COLUMN:
                $old = $edit['column_value_old'];
                $new = $ci->get(ciColumnType::getName($edit['column_id']));
                if ($old == $new) 
                    $noop = true;
                $old = $this->formatHistoryValue($old, $edit['column_id']);
                $new = $this->formatHistoryValue($new, $edit['column_id']);
                    
                $desc = "Changed value of column " . ciColumnType::getName($edit['column_id']) . " from " . $old . " to " . $new;
                break;
                    
            case CI_ACTION_CHANGE_TYPE:
                $old = $edit['type_id_old'];
                $new = $ci->ci_type_id;
                if ($old == $new) 
                    $noop = true;
                $old = htmlEncode(ciType::getName($old));
                $new = htmlEncode(ciType::getName($new));
					
                $desc = "Changed type from " . $old . " to " . $new;
                break;
                    
            }
            if (!$noop) {
                $username = makeLink(array('controller'=>'user','id'=>$edit['user_id']), $edit['username']);
                $time = util::date_format($edit['create_time']);
                $buttons =  makeLink(array('task'=>'view', 'revision_id' => $edit['id']),'Show', 'revision');
                //$buttons .= makeLink(array('task'=>'revert', 'revision_id' => $edit['id']),'Revert', 'revert');
                $content.= "
<tr>
<td>$desc</td>
<td>$time</td>
<td>$username</td>
<td>$buttons</td>
</tr>
";
            }
            
            $ci->apply($edit);
        }

        $content .= "</table>\n";

        //        $content .= "<iframe width='500' src='http://www.google.com'>";
        

        $this->show($action_links,
                    $content);
        
    }

    function viewWrite() 
    {
        $ci = $this->getCi();

        
        $edit = param("task", 'view')=='edit';
        $revision_id = param('revision_id');
        $revision = $revision_id !== null;

        $action_links = $this->getActionMenu($edit);

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
            $content .= "<input type='hidden' name='id' value='".$this->id."'>";
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
                
                $form = $this->makePopupForm('type',$type_select, 'type_select', $type_popup_id);            
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
										
                    $form = $this->makePopupForm($key,  form::makeInput('value', $value, $key, false, "value_$key"), "value_$key", $column_popup_id);
                    $content .= makePopup("Edit " . ciColumnType::getName($key), "Edit", $form, 'edit', 'Edit this CI field', $column_popup_id);
                }
            }
            
            $content .= "</td></tr>";
        }
        
        if (!$edit) {
            
            $content .= $this->makeDependencies();
						
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
            $content .= $this->makeIframes();
						
        }
		
        if (!$edit) {
            $content .= $this->makeFigures();
        }
        
        $this->show($action_links,
                    $content);
    }

    /**
     * Print iframes.
     */
		
    function makeIframes() 
    {
				
        $ci = $this->getCi();
        $content = "";
				
        foreach($ci->_ci_column as $key => $value) {
								
            if (ciColumnType::getType($key) == CI_COLUMN_IFRAME) 
                {
                    $content .= "<div class='iframe_label'>\n";
								
                    $content .= "<span class='iframe_header'>".ciColumnType::getName($key)."</span>";
								
                    static $iframe_popup_form_id=0;
                    $iframe_popup_id = "iframe_popup_form_$iframe_popup_form_id";
                    $iframe_popup_form_id++;
								
                    $form = $this->makePopupForm($key,  form::makeInput('value', $value, $key, false, "value_$key"), "value_$key", $iframe_popup_id);
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
    function makeDependencies()
    {
				
        $revision_id = param('revision_id');
        $revision = $revision_id !== null;
        $ci = $this->getCi();
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
                $content .= makeLink(array('task'=>'removeDependency', 'dependency_id'=>$item->id), "Remove",'remove', "Remove dependency from $my_name to $other_name");
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
            $content .= makeForm($form, array('controller'=>'ci', 'task'=>'addDependency','id'=>$this->id));
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
                $content .= makeLink(array('task'=>'removeDependant', 'dependant_id'=>$item->id), "Remove",'remove', "Remove dependency from $other_name to $my_name");
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
            $content .= makeForm($form, array('controller'=>'ci', 'task'=>'addDependant','id'=>$this->id));
						
						
            $content .= "</td></tr>";
        }
        return $content;
						
    }
		
    /**
     * Print links to appropriate figures.
     */
		
    function makeFigures() 
    {
        $revision_id = param('revision_id');
        $revision = $revision_id !== null;
        $ci = $this->getCi();
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
            $content .= $this->makeChart('chart.php?legend=true', "Legend for the above figure");
								
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