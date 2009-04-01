<?php

require_once 'Image/GraphViz.php';


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
    
    /**
     Creates a new graph object with the settings that we use
    */
    function graph($name) {
        return new Image_GraphViz(true, array('nodesep'=>'0.1', 'fontname'=>'sans-serif','bgcolor'=>'white'), $name);
    }

    /**
     Renders the graph to 'standard output'. 
    */
    function imageKludge($graph, $format) 
    {
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
            $res = $graph->fetch($format);
            $res = preg_replace('/font-size:[ 0-9.]*;/','font-size:10px;', $res);
            return $res;
        }
        else 
            {
                return $graph->fetch($format);
            }
	
    }


    
    function __construct($root, $reverse, $highlight, $steps) 
    {
        $this->root = $root;
        $this->reverse = $reverse;
        $this->highlight = $highlight;
        $this->steps = $steps;
    }
    
    function getName()
    {
        if($this->root == 'full') {
            $name = "all".($this->reverse?'_reverse':'');
        } else {
            $name = "ci_".$this->root->id.($this->reverse?'_reverse':'');
        }			
        $revision_id = param('revision_id');
        if ($revision_id) {
            $name .= "_revision" . $revision_id;
        }
        return $name;
    }
    

    function render($format) 
    {
        $name = $this->getName();
        
        $graph = self::graph($name);
        if($this->root == 'full') {
            $this->renderAll($graph);
        } else {
            $this->renderInternal($graph, $this->root);
        }
        
        $key = sha1(file_get_contents($graph->saveParsedGraph()).$format);
        
        $res = CiGraphCache::get($key);
        
        if ($res) {
            return ($format=='png'?base64_decode($res):$res);
        }
        
        $res = self::imageKludge($graph, $format);		
        
        CiGraphCache::set($key, ($format=='png'?base64_encode($res):$res));
        return $res;
    }
    
    function renderInternal($graph, $node) 
    {
        $this->renderNode($graph, $node, array(), true);
    }
	
    function renderAll($graph) 
    {
        $done = array();
        $ci_list = ci::fetch();
        foreach($ci_list as $ci) {					
            $this->renderNode($graph, $ci, $done, false);
        }
			
    }
	
    function renderNode($graph, $node, $done, $is_root, $depth=0) 
    {
		
        if (array_key_exists($node->id, $done)) {
            return;
        }
        $max_depth = Property::get("chart.maxDepth");
		
        if ($max_depth > 0 && $depth >= $max_depth) 
            {
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
            $this->renderNode($graph, $child, $done, false, $depth+1);
            $graph->addEdge(array($node->getDescription(true) => $child->getDescription(true)),array ( 'arrowhead'=>($this->reverse?'normal':'inv')));
        }
    }

    /**
     Create a legend of all node types.
    */
    function renderLegend($format)
    {
        $graph = self::graph();
        
        foreach(ciType::getTypes() as $type_id => $type_name) {
            $graph->addNode($type_name,array('shape'=>ciType::getShape($type_id),
                                             'fontsize' => '10',));
        }
        return ciChart::imageKludge($graph, $format);
    }

}

?>