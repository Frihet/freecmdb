<?php

class CiListController
extends Controller
{

	private $ci_list = null;
	private $extra_column = array();
	

	function get_ci_list()
	{
		if(!$this->ci_list) 
		{
                    $item_count = Property::get('pager.itemsPerPage', 20);
                    $offset = $item_count * (param('page',1)-1);
                    
		    $arr = array();
                    
		    $filter_type = param('filter_type');
		    $filter_column = param('filter_column');
		    $filter_column_value = param('filter_column_value');
		    $filtered = false;
		    
		    if ($filter_column !== null && $filter_column_value) {
			$filtered = true;
			$arr['filter_column'] = array($filter_column,$filter_column_value);
		    }
		    
		    if ($filter_type !== null && $filter_type >= 0) {
			$filtered = true;
			$arr['filter_type'] = $filter_type;
		    }
                    
                    $arr['limit'] = $item_count;
                    $arr['offset'] = $offset;
                    //message(sprint_r($arr));
                    
		    $this->ci_list = ci::fetch($arr);
		    $this->ci_total_count = ci::fetch($arr + array('count'=>true));
		}
		
		return $this->ci_list;
		
	}

	function addColumn($name, $desc)
	{
		$this->extra_column[] = array($name, $desc);
	}
	

	function viewRun() 
	{
            
		$ci_list = $this->get_ci_list();
		
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
		
                $pager = util::makePager($this->ci_total_count);
                $content .= $pager;
                	
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
</th>";
		
		foreach($this->extra_column as $column) 
		{
			$desc = htmlEncode($column[1]);
			$content .= "<th>
$desc
</th>";
			
		}
		

		$content .= "
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
			
			$content .= "</td>";

			foreach($this->extra_column as $column) 
			{
				$col = $column[0];
				$val = $ci->$col;
				$content .= "<td>
$val
</td>";
				
			}
		

			$content .= "<td>";
			$content .= util::date_format($ci->update_time);
			$content .= "</td><td>";

			$content .= makeLink(array('controller' => 'ci', 'id' => $ci->id,'task'=>'remove'),
								 'Remove', 'remove', "Remove the CI " . $ci->getDescription(),
								 array('onclick'=>'return confirm("Are you sure?");'));
			$content .= "</td></tr>";
		}

		$content .= "</table>";
		}

		if (count($ci_list) < (int)Property::get("chart.maxItems")){
		    $content .= $this->makeChart($ci_list);
		}
		
		$this->show(array(makeLink("?controller=ci&amp;task=create", "Create new item", null),
                                  makeLink("?task=recentlyDeleted", "View recently deleted items", null)), 
			    $content);

	}

        function recentlyDeletedRun()
        {
	    $this->render("recentlyDeleted");
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
