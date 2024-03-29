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
	
	$d = json_encode($controller->getDrilldownInfo());
	$node = $controller->getRoot();
	
	ob_start();
        if(!$controller->isEmbeded()) {
            echo "
<div class='widget widget_2'>
<div class='widget_header'>
<h2>
<h2>".("CMDB")."</h2>
</h2>
</div>
<p>
";
            }
        
	
	?>
<style>
<?php
        require('plugins/drilldown/static/drilldown.css');
?>
</style>
<?php
        $link = makeLink(array('plugin'=>null,'controller'=>ci,'id'=>$node->id),$node->getDescription());
        $root_id = htmlEncode($controller->embedPoint());
        echo "<div id='$root_id'><div class='drilldown_expand expanded'></div></div>";
?>
<script src="/FreeCMDB/plugins/drilldown/static/drilldown.js" ></script>
<script>

var drilldownData = <?=$d?>;
var drilldownBaseUrl = <?=json_encode(makeUrl(array('plugin'=>null,'controller'=>ci,'id'=>'')))?>;
var drilldownIsEmbeded = <?=json_encode($controller->isEmbeded())?>;
var drilldownUpdateTarget = <?=json_encode($controller->updateTarget())?>;
freecmdbDrilldownAdd(<?=$node->id?>, $('#<?= $controller->embedPoint() ?>')[0],[]);
 
</script>
      <?php

        $content = ob_get_contents();
        ob_end_clean();
	if($controller->isEmbeded())
	{
	    echo $content;
	    exit(0);
	}
	
	$controller->show($controller->getActionMenu(), $content);
        
    }

}

?>