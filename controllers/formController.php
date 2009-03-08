<?php

/**
 Ajax-controller for updating the contrnts of list-style columns.
 */
class formController
extends Controller
{
    function addColumnListItemRun() 
    {
        $column_id=param('column_id');
        $value=param('value');
        
        
        ciColumnList::addItem($column_id, $value);
                
        $this->viewRun();
    }

    function updateColumnListItemRun() 
    {
        $id=param('id');
        $value=param('value');
        ciColumnList::updateItem($id, $value);			
        $this->viewRun();
    }

    function removeColumnListItemRun() 
    {
        $id=param('id');
        $column_id=param('column_id');
        ciColumnList::removeItem($id, $column_id);        
        $this->viewRun();
    }
    
    function viewRun()
    {
        ob_end_clean();
        foreach(ciColumnList::getItems(param('column_id')) as $id => $name) {
            echo "$id\t$name\n";
        }
        exit(0);
    }

    function fetchListTableRun()
    {
        ob_end_clean();
        echo form::makeColumnListEditor(param('column_id'), param('select_id'), param('table_id'));
        exit(0);
    }

}

?>