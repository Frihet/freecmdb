<?php
  /**
   Print dependency graphs for a selection of CIs.
  */

require_once("config.php");
require_once("common/util/util.php");
require_once("common/util/db.php");
require_once("common/model.php");
require_once("model.php");
require_once("ciChart.php");

function main() 
{
    $format = param('format','svg');
    
    if($format=='svg') {
        header('Content-Type: image/svg+xml');
    }
    
    if (param('legend')) {
        echo ciChart::renderLegend($format);
    }
    else {
        $full = param('full',null);
			
        $ci_id = param('id');
        $ci_list = ci::fetch(array('id_arr'=>array($ci_id)));
        $ci = $ci_list[$ci_id];
			
        $c = new ciChart(($full=='yes')?'full':$ci, 
                         param('mode','dependencies')!='dependencies',
                         param('highlight',array()), 
                         param('steps'));
        echo $c->render($format);
    }
}

db::init(DB_DSN) || die("The site is down. Reason: Could not connect to the database.");
db::query("set client_encoding to \"utf8\"");

ciUser::init();
main();

?>