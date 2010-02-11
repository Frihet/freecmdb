<?php

class recentlyDeletedView
	extends View
{

	function render($controller)
	{
	    $content = "
<table class='striped history_table'>
<tr>
<th>
"._("Item")."
</th>
<th>
"._("Deletion time")."
</th>
<th>
"._("Deleted by")."
</th>
<th>
</th>
</tr>
";
	    
	    foreach(history::fetchRemoves() as $rem) {
                $time = FreeCMDB::dateTime($rem['create_time']);
                $username = makeLink(array('controller'=>'user','id'=>$rem['user_id']), $rem['username']);
		
		$buttons =  makeLink(array('controller'=>'ci','task'=>'view','id'=>$rem['ci_id']),_('Show'), 'revision');
                $buttons .= makeLink(array('task'=>'undelete', 'target_revision_id' => $edit['id']),_('Undelete'), 'revert');

		$content .= "<tr>
<td>
{$rem['name']}
</td>
<td>
$time
</td>
<td>
$username
</td>
<td>
$buttons
</td>
</tr>
";
		
	    }
	    
	    $content .= "</table>";
	    


	    $controller->show(array(makeLink("?controller=ci&amp;task=create", _("Create new item"), null),
				    makeLink("?task=recentlyDeleted", _("View recently deleted items"), null)), 
			      $content);
	    
	}

}

?>