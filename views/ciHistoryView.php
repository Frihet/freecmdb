<?php

class ciHistoryView
{

    function formatHistoryValue($val, $column_id) 
    { 
       
        if ($val ==null) {
            return "<em class='value'>&lt;"._("empty")."&gt;</em>";
        }
        
        $type = ciColumnType::getType($column_id);
        if ($type != CI_COLUMN_IFRAME) {
            $val = ciView::makeInput(null, null, $val, $column_id, true);
            $val = strip_tags($val);
        }        
        
        
        if(strlen($val) > 64) {
            $val = substr($val, 0, 60) . "...";
        }
        
        $res = "<em class='value'>«".htmlEncode($val)."»</em>";

        return $res;
        
    }


    function render($controller) 
    {
        $edits = $controller->getEdits();
        $ci = $controller->getCi();
        $action_links = $controller->getActionMenu();
        util::setTitle("History for " . $ci->getDescription());
	
        $content = "
<table class='striped history_table'>
<tr>
<th>
"._("Description of change")."
</th>
<th>
"._("Time")."
</th>
<th>
"._("Changed by")."
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
                if ($edit['ci_id'] == $controller->id) {
                    $desc = sprintf(_("Added dependency to ci %s"), $edit['dependency_name']);
                }
                else {
                    $desc = sprintf(_("Added dependency from ci %s"), $edit['dependant_name']);
                }
                
                break;
            case CI_ACTION_REMOVE_DEPENDENCY:
                if ($edit['ci_id'] == $controller->id) {
                    $desc = sprintf(_("Removed dependency to ci %s"), $edit['dependency_name']);
                }
                else {
                    $desc = sprintf(_("Removed dependency from ci %s"), $edit['dependant_name']);
                }
                break;

            case CI_ACTION_CHANGE_COLUMN:
            
                $old = $edit['column_value_old'];
                $new = $ci->get(ciColumnType::getName($edit['column_id']));
                
                if ($old == $new) 
                    $noop = true;
                $old = $this->formatHistoryValue($old, $edit['column_id']);
                $new = $this->formatHistoryValue($new, $edit['column_id']);
                
                $desc = sprintf(_("Changed value of column %s from %s to %s"), ciColumnType::getName($edit['column_id']), $old, $new);
                break;
                    
            case CI_ACTION_CHANGE_TYPE:
                $old = $edit['type_id_old'];
                $new = $ci->ci_type_id;
                if ($old == $new) 
                    $noop = true;
                $old = htmlEncode(ciType::getName($old));
                $new = htmlEncode(ciType::getName($new));
					
                $desc = sprintf(_("Changed type from %s to %s"), $old, $new);
                break;
                    
            }
            if (!$noop) {
                $username = makeLink(array('controller'=>'user','id'=>$edit['user_id']), $edit['username']);
                $time = FreeCMDB::dateTime($edit['create_time']);
                $buttons =  makeLink(array('task'=>'view', 'revision_id' => $edit['id']),_('Show'), 'revision');
                $buttons .= makeLink(array('task'=>'revert', 'target_revision_id' => $edit['id']),_('Revert'), 'revert');
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
        

        $controller->show($action_links,
                          $content);
		

    }
	

}



?>