<?php

class DrilldownCiSelector
{
    function make($name, $selected)
    {
        return '<input type="hidden" name="' . htmlEncode($name) . '" id="' . htmlEncode($name) . '" value="' . htmlEncode($selected) . '" />
    <div id="ci_picker_code"></div>
    <script>
      $("#ci_picker_code").load("/FreeCMDB/plugins/drilldown/drilldown?update_target='.htmlEncode($name).'&embed_point=ci_picker_root&time='.time().'");
    </script>

';
        
    }

}



class drilldownPlugin
	extends Plugin
{

    function startupHandler($param)
    {
        $app = $param['source']->getApplication();
        $app->setCiSelector(new DrilldownCiSelector());        

        $param['source']->addContent('action_menu_pre',"<li>".makeLink(makeUrl(array('plugin'=>'drilldown','controller'=>'drilldown','id'=>null)),
                                                                             _("Drilldown"))
                                     . "</li><li><hr/></li>");
        
                

    }
    
}

?>