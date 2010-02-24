<?php

class CiListController
extends CmdbController
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

                    if(param('q') !== null) {
                        $filter_column='4';
                        $filter_column_value=param('q');
                    }
		    
		    if ($filter_column !== null && $filter_column_value) {
			$filtered = true;
			$arr['filter_column'] = array($filter_column,$filter_column_value);
		    }
		    
		    if ($filter_type !== null && $filter_type >= 0) {
                        $filtered = true;
			$arr['filter_type'] = $filter_type;
		    }

                    if(param('output',null) !== 'csv' &&
                       param('output',null) !== 'autocomplete' ) {
                        $arr['limit'] = $item_count;
                        $arr['offset'] = $offset;
                    }
                    
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
	

        function to_json($ci_list, $tot_count, $offset) 
        {
            $ci_list_transformed = array();
            foreach($ci_list as $it) {
                $ci_list_transformed[] = array('name'=>$it->getDescription(false),
                                               'description'=>$it->getDescription(true),
                                               'url'=>makeUrl(array("controller"=>"ci", "task"=>"view",'id'=>$it->id)));
            }
            

            $rs = array('totalResultsAvailable'=>$tot_count,
                       "totalResultsReturned"=>count($ci_list),
                       "firstResultPosition"=>$offset,
                       "Result"=>$ci_list_transformed);
            return json_encode(array("ResultSet"=>$rs));
            
        }
        
	function viewRun() 
	{
            $ci_list = $this->get_ci_list();
            if(param('output') == 'json') {
                echo $this->to_json($ci_list, $this->ci_total_count, param('page', 0));
                exit(0);
            } else if (param('output') == 'autocomplete') {
                foreach($ci_list as $it) {
                    echo $it->id ." - " .$it->getDescription(true);
                    echo "\n";
                    
                }
                exit(0);
            } else if (param('output') == 'csv') {
                $col = CiColumnType::getColumns();
                
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=CiList.csv");
                header("Pragma: no-cache");
                header("Expires: 0");

                echo '"'.implode('","', $col)."\"\n";
                
                foreach($ci_list as $it) {
                    $first = true;
                    foreach($col as $id => $col_name) {
                        if ($first) {
                            $first = false;
                        }
                        else {
                            echo ",";
                        }
                        echo "\"";
                        
                        if(CiColumnType::getType($id) == CI_COLUMN_LIST) {
                            $value = ciColumnList::getName($it->_ci_column[$id]);
                        } else {
                            $value = $it->_ci_column[$id];
                        }
                        echo addcslashes($value,"\"\r\n");
                        
                        echo "\"";
                        
                    }
                    echo "\n";
                }
                exit(0);
            }
            
		
            util::setTitle("View CIs");
            $form = "";

		
            $form .= "
<table class='striped'>
<tr>
<th>
<label for='filter_type'>"._("Filter on type")."</label>
</th>
<td>
";
            $form .= form::makeSelect('filter_type',
                                      array(-1=>_('None'))+ciType::getTypes(), 
                                      param('filter_type', -1),
                                      'filter_type');
            $form .= "
</td>
</tr>
<tr>
<th>
<label for='filter_column'>"._("Filter on column value")."</label>
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
            $form .= "<div class='button_list'><button type='submit'>"._("Filter")."</button></div>";
            
            $content .= form::makeForm($form,array('controller'=>'ciList'), 'get');
            
            $pager = util::makePager($this->ci_total_count);
            $content .= $pager;
            
            if (!count($ci_list)) 
		{
                    $content .= _("No CIs matched your criteria!");
		}
            else 
		{
			
		$content .= "
<table class='striped ci_list_table'>
<tr>
<th>
"._("Item")."
</th>
<th>
"._("Type")."
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
"._("Last updated")."
</th>
<th>
</th>
</tr>

";

		foreach($ci_list as $ci) {
			$content .= "<tr>";
			$content .= "<td>";
            
			$content .= makeLink(array('controller' => 'ci', 'id' => $ci->id),$ci->getDescription(false), null, sprintf(_("View detailed information for the CI %s."), $ci->getDescription(false) ));
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
			$content .= FreeCMDB::dateTime($ci->update_time);
			$content .= "</td><td>";
                        if(ciUser::can_edit()) {
                            
                            $content .= makeLink(array('controller' => 'ci', 'id' => $ci->id,'task'=>'remove'),
                                                 _('Remove'), 'remove', sprintf(_("Remove the CI %s"), $ci->getDescription()),
                                                 array('onclick'=>'return confirm("'.addcslashes(_("Are you sure?"),'"\'').'");'));
                        }
                        
			$content .= "</td></tr>";
		}
                $content .= "<tr><td colspan='4'>";
                
                $content .= makeLink(makeUrl(array('output'=>'csv',
                                                   'filter_type'=>param('filter_type'),
                                                   'filter_column'=>param('filter_column'),
                                                   'filter_column_value'=>param('filter_column_value'))),
                                     _('Download as CSV'));
                
                $content .= "</td></tr>";

		$content .= "</table>";
		}

		if (count($ci_list) < (int)Property::get("chart.maxItems")){
		    $content .= $this->makeChart($ci_list);
		}
		
                $actions = array();
                if(ciUser::can_edit()) {
                    $actions[] = makeLink(makeUrl(array("controller"=>"ci", "task"=>"create")), _("Create new item"), null);
                }
                $actions[] = makeLink(makeUrl(array("controller"=>"ciList", "task" => "recentlyDeleted")), _("View recently deleted items"), null);
                

		$this->show( $actions,
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

                $url = util::getPath()."chart.php?full=yes$highlight_str";
		return "
<div class='figure'>
<object data='$url' type='image/svg+xml'>
<img src='$url&format=png'/>
</object>
<span class='caption'>All CIs and their dependencies</span>
</div>
";
	}
	
}

?>
