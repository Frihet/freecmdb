<?php

class configureView
	extends simpleConfigureView
{

    function generateFormElement($prop_name, $form_name, $value) 
    {
        return "<input name='".htmlEncode($form_name)."' value='".htmlEncode($value)."'/>";
    }

}

?>