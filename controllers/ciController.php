<?php
  /**
   Controller for ci objects
  */
class CiController
extends CmdbController
{
    var $id;
 
    function __construct() 
    {
        $this->id = param('id');
        
    }

    function addDependencyRun()
    {
        ciUser::assert_edit();
        $other_id = param('dependency_id');
        list($type_id,$reverse) = explode(':',param('dependency_type_info'));
	if($reverse) 
	{
	    $ci_list = ci::fetch(array('id_arr'=>array($other_id)));
	    $ci = $ci_list[$other_id];
	    $ci->addDependency($this->id, $type_id);
	    message(_("Dependant added"));
	}
	else 
	{
	    $ci = $this->getCi();
	    $ci->addDependency($other_id, $type_id);
	    message(_("Dependency added"));
	}
        util::redirect(makeUrl(array('task'=>null, 'dependency_id'=>null)));
    }

    function removeDependencyRun()
    {
        ciUser::assert_edit();
        $other_id = param('dependency_id');
        $ci = $this->getCi();
        $ci->removeDependency($other_id);
        message(_("Dependency removed"));
        util::redirect(makeUrl(array('task'=>null, 'dependency_id'=>null)));
    }

    function removeDependantRun()
    {
        ciUser::assert_edit();
        $other_id = param('dependant_id');
        $ci_list = ci::fetch(array('id_arr'=>array($other_id)));
        $ci = $ci_list[$other_id];
        $ci->removeDependency($this->id);
        message(_("Dependant removed"));
        util::redirect(makeUrl(array('task'=>null, 'dependency_id'=>null)));
    }
    
    function updateField($key, $value)
    {
        $ci = new ci();
        $ci->id=param('id');
        if ($key=='type') {
            return $ci->setType(param('type'));
        } else {
            if ($value !== null) {
                return $ci->set($key, $value);
            } else {
                return $ci->deleteValue($key);
            }
        }
    }
    
    function updateFieldFile($key, $value)
    {
        $ci = new ci();
        $ci->id=param('id');
        if ($value !== null) {
            return $ci->setFile($key, $value);
        } else {
            return $ci->deleteValueFile($key);
        }
    }
    
    function updateFieldRun()
    {
        ciUser::assert_edit();
        $key = param('key');
        if(array_key_exists('value', $_FILES)){
            $this->updateFieldFile($key, $_FILES['value']);            
        } else {
            $value = param('value');
            $this->updateField($key, $value);
        }
        
        message(_('CI updated'));
        util::redirect(makeUrl(array('controller'=>'ci', 'id'=>$this->id, 'task'=>null, 'key'=>null, 'value' => null)));
    }
    
    function createRun()
    {
        ciUser::assert_edit();
        db::query("insert into ci (ci_type_id) select id from ci_type where deleted=false limit 1");
        $id = db::lastInsertId("ci_id_seq");
        
        log::add($id, CI_ACTION_CREATE);
        $this->id = $_GET['id'] = $id;

        message(_('CI created'));
        util::redirect(makeUrl(array('task'=>'edit', 'key'=>null, 'value' => null, 'id' => $id)));
    }
    
    function getCi() 
    {
        if ($this->ci != null) {
            return $this->ci;
        }
        $ci_list = ci::fetch(array('id_arr'=>array($this->id), 'deleted'=>true));
        $this->ci = $ci_list[$this->id];
        return $this->ci;
    }

    function saveAllRun() 
    {
        ciUser::assert_edit();
        $arr = array('controller'=>'ci', 'id'=>$this->id, 'task'=>null, 'dependency_id'=>null);
        db::begin();
        $ok = true;
        
        foreach($_REQUEST as $key => $value) {
            $prefix = substr($key, 0, 6);
            $suffix = substr($key, 6);
            if ($prefix == 'value_') {
                $ok &= $this->updateField($suffix, $value);
                $arr[$key] = null;
            }
        }

        foreach($_FILES as $key => $value) {
            $prefix = substr($key, 0, 6);
            $suffix = substr($key, 6);
            if ($prefix == 'value_') {
                $ok &= $this->updateFieldFile($suffix, $value);
                $arr[$key] = null;
            }
        }
        

        $type_id = param('type');
        if ($type_id) {
            db::query('update ci set ci_type_id=:type_id where id=:id',
                      array(':type_id'=>$type_id,':id'=>param('id')));

        }
        if( $ok) {
            db::commit();
            message(_('Fields updated'));
            util::redirect(makeUrl($arr));
        }
        else {
            db::rollback();
            error(_('Fields not updated'));
            $_REQUEST['task'] = 'edit';
            $this->viewRun();
        }
        
    }
    
    function removeRun()
    {
        ciUser::assert_edit();
        $ci = $this->getCi();
        if ($ci->delete()) {
            message(_('CI removed'));
        }
        else {
            error(_('Could not remove CI, not found.'));
        }
        
        util::redirect(makeUrl(array('id'=>null, 'controller'=>'ciList', 'task'=>null)));
    }
    
    function makePopupForm($field_name, $input, $input_id, $popup_id)
    {
        
        $form = "<p><label for='$input_id'>New value:</label></p><p>" . $input;
        $form .= "</p><p><button type='submit'>Save</button> ";
        $form .= "<button type='button' onclick='popupHide(\"$popup_id\")'>"._("Cancel")."</button></p>";
        
        return form::makeForm($form, array('task'=>'updateField','controller'=>'ci','id'=>$this->id,'key'=>$field_name), 'post', true);
    }
    
    function getActionMenu() 
    {
        $action_links = array();
	
        $task = param('task','view');
                
        $action_links[] = makeLink(makeUrl(array("controller"=>"ciList")), _("View all"));

        if($task!='view') {
            $action_links[] = makeLink(array('task'=>'view','revision_id'=>null), _("View"), 'view', _('Go back to read-only view of this CI'));
        }
        

        if ($task != 'edit' && ciUser::can_edit()) {
            $action_links[] = makeLink(array('task'=>'edit','revision_id'=>null), _("Edit"), 'edit',  _('Edit all fields of this CI'));
        }
        if($task != 'history') {
            $action_links[] = makeLink(array('task'=>'history','revision_id'=>null), _("History"), null, _('Show the entire revision history for this item.'));
        }

        if(ciUser::can_edit()) {
            $action_links[] = makeLink(makeUrl(array("controller"=>"ci", "task"=>"create")), _("Create new item"), null, _("Creat an empty new CI"));
            $action_links[] = makeLink(array('task'=>'remove','revision_id'=>null), _("Remove"), 'remove', _('Remove this CI'), array('onclick'=>'return confirm("'.addcslashes(_("Are you sure?"),'"\\').'");'));
            $action_links[] = makeLink(array('task'=>'copy','revision_id'=>null), _("Copy"), 'copy', _('Copy this CI'));
        }
        
        return $action_links;
        
    }
    
    function copyRun()
    {
        ciUser::assert_edit();
        $id_orig = param('id');
        db::query('insert into ci (ci_type_id) select ci_type_id from ci where id=:id', array(':id'=>$id_orig));
        $id_new = db::lastInsertId("ci_id_seq");
        
        db::query('
insert into ci_column 
(
        ci_id, ci_column_type_id, value
)
select :new_id, ci_column_type_id, value from ci_column where ci_id = :old_id', array(':old_id'=>$id_orig, ':new_id' => $id_new));

        db::query('
insert into ci_dependency 
(
        ci_id, dependency_id, dependency_type_id
)
select :new_id, dependency_id, dependency_type_id 
from ci_dependency 
where ci_id = :old_id', array(':old_id'=>$id_orig, ':new_id' => $id_new));

        db::query('
insert into ci_dependency 
(
        ci_id, dependency_id, dependency_type_id
)
select ci_id, :new_id, dependency_type_id 
from ci_dependency 
where dependency_id = :old_id', array(':old_id'=>$id_orig, ':new_id' => $id_new));
        
        util::redirect(makeUrl(array('id'=>$id_new, 'task'=>null)));
	
        /*
         FIXME: Update default column with shiny new value, e.g. append (copy) to it or something...
        */
    }
	
    function editRun()
    {
        ciUser::assert_edit();
        $this->viewRun();
    }
    
    function revertRun()
    {
        ciUser::assert_edit();
        $ci = $this->getCi();
        $revision_id = param('target_revision_id');
        
        $edits = db::fetchList('
select cl2.ci_id, cl2.action, cl2.type_id_old, cl2.column_id, cl2.column_value_old, cl2.dependency_id
from ci_log as cl
join ci_log as cl2
on cl2.create_time >= cl.create_time 
where cl.id=:revision_id and (cl2.ci_id = :ci_id or cl2.dependency_id = :ci_id) 
order by cl2.create_time desc;', 
                               array(':ci_id'=>$this->id, ':revision_id'=>$revision_id));
        
        $ci_orig = clone($ci);
        
        $delete = false;
        
        foreach($edits as $edit) {
            
            if ($edit['action'] == CI_ACTION_CREATE) {
                $delete = true;
            }
            
            $ci->apply($edit);
        }

        $ok = true;
        db::begin();
        
        if ($ci->ci_type_id != $ci_orig->ci_type_id) {
            $ok &= $this->updateField('type', $ci->ci_type_id);
        }
        
        foreach($ci->_ci_column as $key=>$value) {
            $old_value = $ci_orig->_ci_column[$key];
            //echo "Column $key: NEW $value, old $old_value<br>";
            
            if ($value !== $old_value) {
                $ok &= $this->updateField($key, $value);
                //echo "Change column value of $key from $old_value to $value<br>" ;
            }
        }
        if ($delete) {
            //echo "Delete CI {$ci->id}";
            $ok &= $ci->delete();
        }
        
        if ($ok) {
            db::commit();
            if (!$delete) {
                util::redirect(makeUrl(array('task'=>null, 'revision_id'=>null)));
            }
            else {
                util::redirect(makeUrl(array('task'=>null, 'to_revision_id'=>null, 'controller' => 'ciList', 'id'=>null)));
            }
        }
        else {
            db::rollback();
            util::redirect(makeUrl(array('task'=>'history','to_revision_id'=>null)));
        }
    }
    
    function getEdits() 
    {
        return history::fetch($this->id);
    }
	    
    function historyRun()
    {
        $this->render("ciHistory");
    }
	
    function viewRun()
    {
        $this->render("ci");
    }
	
}

?>
