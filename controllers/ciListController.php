<?php

class ciListController
extends Controller
{

	function viewWrite() 
	{
		$arr = array();
			
		$filter_type = param('filter_type');
		$filter_column = param('filter_column');
		$filter_column_value = param('filter_column_value');
		$filtered = false;
			
		if ($filter_column !== null && $filter_column_value) 
		{
			$filtered = true;
			$arr['filter_column'] = array($filter_column,$filter_column_value);
		}

		if ($filter_type !== null && $filter_type >= 0) 
		{
			$filtered = true;
			$arr['filter_type'] = $filter_type;
		}



		$ci_list = ci::fetch($arr);
                util::setTitle("View CIs");
		$form = "";
		
		$form .= "
<table class='striped'>
<tr>
<th>
<label for='filter_type'>Filter on type</label>
</th>
<td>
";
		$form .= form::makeSelect('filter_type',
					  array(-1=>'None')+ciType::getTypes(), 
					  param('filter_type', -1),
					  'filter_type');
		$form .= "
</td>
</tr>
<tr>
<th>
<label for='filter_column'>Filter on column value</label>
</th>
<td>
";
		
		$form .= form::makeSelect('filter_column',
					  ciColumnType::getColumns(), 
					  param('filter_column', -1),
					  'filter_column');
		$form .= form::makeText('filter_column_value', 
					param('filter_column_value', ''));
		$form .= "
</td>
</th>
</table>
";
		$form .= "<div class='button_list'><button type='submit'>Filter</button></div>";
			
		$content .= form::makeForm($form,array('controller'=>'ciList'), 'get');
		
	
		if (!count($ci_list)) 
		{
			$content .= "No CIs matched your criteria!";
		}
		else 
		{
			
		$content .= "
<table class='striped ci_list_table'>
<tr>
<th>
Item
</th>
<th>
Type
</th>
<th>
Last updated
</th>
<th>
</th>
</tr>

";

		foreach($ci_list as $ci) {
			$content .= "<tr>";
			$content .= "<td>";
            
			$content .= makeLink(array('controller' => 'ci', 'id' => $ci->id),$ci->getDescription(false), null, "View detailed information for the CI " . $ci->getDescription(false) );
			$content .= "</td><td>";
			$content .= ciType::getName($ci->ci_type_id);
			
			$content .= "</td><td>";
			$content .= util::date_format($ci->update_time);
			$content .= "</td><td>";

			$content .= makeLink(array('controller' => 'ci', 'id' => $ci->id,'task'=>'remove'),'Remove', 'remove', "Remove the CI " . $ci->getDescription(),array('onclick'=>'return confirm("Are you sure?");'));
			$content .= "</td></tr>";
            
		}
		$content .= "</table>";
		}
		
		$content .= $this->makeChart($filtered?$ci_list:array());
		

		$this->show(array(makeLink("?controller=ci&amp;task=create", "Create new item", null, "Creat an empty new CI")), 
			    $content);

	}

	function makeChart($highlight) 
	{
		$highlight_str = "";
		foreach($highlight as $ci) 
		{
			$highlight_str .= "&highlight[]=" . $ci->id;
		}
						
		return "
<div class='figure'>
<object data='chart.php?full=yes$highlight_str' type='image/svg+xml'>
<img src='chart.php?full=yes&format=png$highlight_str'/>
</object>
<span class='caption'>All CIs and their dependencies</span>
</div>
";
	}
	
}

?>
