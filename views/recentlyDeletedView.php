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
Item
</th>
<th>
Deletion time
</th>
<th>
Deleted by
</th>
<th>
</th>
</tr>
";
	    
	    foreach(history::fetchRemoves() as $rem) {
                $time = util::date_format($rem['create_time']);
                $username = makeLink(array('controller'=>'user','id'=>$rem['user_id']), $rem['username']);
		
		$buttons =  makeLink(array('controller'=>'ci','task'=>'view','id'=>$rem['ci_id']),'Show', 'revision');
                $buttons .= makeLink(array('task'=>'undelete', 'target_revision_id' => $edit['id']),'Undelete', 'revert');

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
	    


	    $controller->show(array(makeLink("?controller=ci&amp;task=create", "Create new item", null),
				    makeLink("?task=recentlyDeleted", "View recently deleted items", null)), 
			      $content);
	    
	}

}

?>