<?php

  /*
   Navigation tool that shows a drilldown of all CIs 
  */
class DrilldownView
	extends View
{
    /*
     Show the view
    */
    function render($controller)
    {
	
        $action_links = array();
	$d = json_encode($controller->getDrilldownInfo());
	$node = $controller->getRoot();
	
	ob_start();
	
	?>
<style>
<?php
        require('plugins/drilldown/static/drilldown.css');
?>
</style>
<script>
var drilldownData = <?=$d?>;
var drilldownBaseUrl = <?=json_encode(makeUrl(array('plugin'=>null,'controller'=>ci,'id'=>'')))?>;
var drilldownIsEmbeded = <?=json_encode($controller->isEmbeded())?>;
var drilldownUpdateTarget = <?=json_encode($controller->updateTarget())?>;

<?php
      require('plugins/drilldown/static/drilldown.js');
?>
$(document).ready(function(){
	
	freecmdbDrilldownAdd(<?=$node->id?>, $('#<?= $controller->embedPoint() ?>')[0],[]);
});
</script>
      <?php

      $link = makeLink(array('plugin'=>null,'controller'=>ci,'id'=>$node->id),$node->getDescription());
	    $root_id = htmlEncode($controller->embedPoint());
	    echo "<div id='$root_id'><div class='drilldown_expand expanded'></div>$link</div>";

        $content = ob_get_contents();
        ob_end_clean();
	if($controller->isEmbeded())
	{
	    echo $content;
	    exit(0);
	}
	
	$controller->show($action_links, $content);
        
    }

}

?>