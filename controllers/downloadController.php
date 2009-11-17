<?php

class downloadController
	extends CmdbController
{

    function viewRun()
    {
        $ci_id = param('ci_id');
        $column_id = param('column_id');
        $data = db::fetchRow("select * from ci_column_file where ci_id = :ci_id and ci_column_type_id = :column_id",
                             array(':ci_id' => $ci_id,
                                   ':column_id' => $column_id));
        message($data);
        $type = $data['type'];
        
        header("Content-Type: $type");
        
        fpassthru( $data['value']);
        exit();
        
    }

}

?>