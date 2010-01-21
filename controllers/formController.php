<?php

/**
 Ajax-controller for updating the contrnts of list-style columns.
 */
class formController
extends CmdbController
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
        $foo = new stdClass();
        $foo->lines = array();
        
        foreach(ciColumnList::getItems(param('column_id')) as $id => $name) {
            $it = new stdClass();
            $it->name = $name;
            $it->id = $id;
            $foo->lines[] = $it;
        }

        $foo->table = ciView::makeColumnListEditor(param('column_id'), param('select_id'), param('table_id'));
        echo json_encode($foo);
        exit(0);
    }

}

?>