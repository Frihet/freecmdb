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
		$ci = new ci();
		$ci->id=param('id');
        if ($key=='type') {
			$ci->setType(param('type'));
        } else {
			$ci->set($key, $value);
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
        
        return form::makeForm($form, array('task'=>'updateField','controller'=>'ci','id'=>$this->id,'key'=>$field_name));
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
        $action_links[] = makeLink(array('task'=>'remove','revision_id'=>null), "Remove", 'remove', 'Remove this CI', array('onclick'=>'return confirm("Are you sure?");'));
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
    
	function getEdits() 
	{
		return history::fetch($this->id);
    }
	    
    function historyWrite()
    {
		$this->render("ciHistory");
	}
	
	function viewWrite()
	{
		$this->render("ci");
	}
	
}

?>
