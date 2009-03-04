<?php
require_once 'Image/GraphViz.php';

$graph = new Image_GraphViz(true, array(), "Dependencies", true, false);

$graph->addNode('Node1',array('URL'   => 'http://link1','label' => 'This is a label','shape' => 'box'));
$graph->addNode('Node2', array('URL' => 'http://link2', 'fontsize' => '14'));
$graph->addNode('Node3',array('URL' => 'index.php','fontsize' => '20', 'target'=>'_parent'));

$graph->addEdge(array('Node1' => 'Node2'), array('label' => 'Edge Label'));
$graph->addEdge(array('Node1' => 'Node2'),array('color' => 'red'));

$graph->image();

?>