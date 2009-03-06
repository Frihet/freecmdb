<?php
  /**
   Print dependency graphs for a selection of CIs.
   */

require_once 'Image/GraphViz.php';

require_once("config.php");
require_once("util/util.php");
require_once("util/db.php");
require_once("model.php");



/**
 Renders the graph to 'standard output'. 
 */
function render($graph) 
{
    $format = param('format','svg');
		
    if( $format=='svg') {	
        /* Ugly, ugly workaround for graphviz/firefox
         bug. It seems that font size requests are ignored
         by some graphviz versions, and that firefox does
         not understand font sizes with no unit
         specified. This string manually overrides the
         font style in the svg, so long as it doesn't
         change in a future graphviz version...

         Let's hoe nobody doe a graph containing css markup as node
         text, or this will confuse the hell out of somebody..
        */
        $res = $graph->fetch(param('format','svg'));
        header('Content-Type: image/svg+xml');
        $res = preg_replace('/font-size:[ 0-9.]*;/','font-size:10px;', $res);
        echo $res;
    }
    else 
        {
            $graph->image($format);
        }
		
}

/**
 Creates a new graph object with the settings that we use
 */
function graph() {
    return new Image_GraphViz(true, array('nodesep'=>'0.1', 'fontname'=>'sans-serif','bgcolor'=>'white'));
}

/** Chart creation object. Waklks around the graph and renders the
 parts it likes.
 */
class ciChart
{
    
    var $root;
    var $level_count;
    var $level_width;

    var $highlight;
    var $steps;
    var $reverse;
    
    
    function __construct($root, $reverse, $highlight, $steps) 
    {
        $this->root = $root;
        $this->reverse = $reverse;
        $this->highlight = $highlight;
        $this->steps = $steps;
    }
    
    function run() 
    {
        $graph = graph();
        if($this->root == 'full') 
            {
                $this->render_all($graph);
            }
        else 
            {
                $this->render($graph, $this->root);
            }
			
        render($graph);		
    }
    
    function render($graph, $node) 
    {
        $this->render_node($graph, $node, array(), true);
    }
	
    function render_all($graph) 
    {
        $done = array();
        $ci_list = ci::fetch();
        foreach($ci_list as $ci) 
            {					
                $this->render_node($graph, $ci, $done, false);
            }
			
    }
	
    function render_node($graph, $node, $done, $is_root) 
    {
        if (array_key_exists($node->id, $done)) {
            return;
        }
        $revision_id = param('revision_id');
        $revision_str = $revision_id !== null? "&revision_id=$revision_id":"";
        
        $graph->addNode($node->getDescription(true),
                        array('URL' => 'index.php?action=ci&id='.$node->id.$revision_str,
                              'target' => '_parent',
                              'shape' => ciType::getShape($node->ci_type_id),
                              'fontsize' => '10', 
                              'fontname' => 'sans-serif',  // Re-add font name attribute here, since GraphViz seems to ignore the main graph attribute, even though the docs say it shouldn't.
                              'label'=>str_replace(' ','\n',$node->getDescription(true)),
                              'color' => ($is_root?'green':(array_key_exists($node->id, $this->highlight)?'green':'black'))
                              )
                        );
        
        
        
        $func = (!$this->reverse)?"getDirectDependencies":"getDirectDependants";
        
        $children = $node->$func();
        
        foreach($children  as $child) {
            $done[$node->id] = $node;
            $this->render_node($graph, $child, $done, false);
            $graph->addEdge(array($node->getDescription(true) => $child->getDescription(true)),array ( 'arrowhead'=>($reverse?'normal':'inv')));
        }
    }
}

/**
 Create a legend of all node types.
 */
function legend()
{
    $graph = graph();

    foreach(ciType::getTypes() as $type_id => $type_name) {
        $graph->addNode($type_name,array('shape'=>ciType::getShape($type_id),
                                         'fontsize' => '10',));
    }
    
    render($graph);
}


function main() 
{
    if (param('legend')) {
        legend();
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
        $c->run();
    }
}
db::init(DB_DSN) || die("The site is down. Reason: Could not connect to the database.");
ciUser::init();
main();

?>