
function submitAndReloadColumnList(task,columnId, rowId, itemId, tableId, selectId) {
    if(task!='removeColumnListItem' || confirm("Are you sure?")){
	var url = FreeCMDB.base + 'index.php?controller=form&task=' + encodeURIComponent(task) + 
	    '&column_id=' + encodeURIComponent(columnId) +
	    '&value=' + encodeURIComponent($("#"+itemId)[0].value) +
	    '&id=' + encodeURIComponent(rowId) + '&select_id='+encodeURIComponent(selectId);
	
	$.getJSON(url,
		  [],
		  function(response) {
		      var lines = response.lines;//transport.responseText.split('\n');
		      var sel = $("#"+selectId)[0];
		      
		      while(sel.length>0) 
			  {
			      sel.remove(0);
			  }
		      
		      for (var i=0; i<lines.length; i++) {
			  var data = lines[i];
			  if( data.id && data.name )
			      sel.add(new Option(data.name, data.id), null);
		      }
		      
		      var oldTab = $("#"+tableId)[0];
		      var parent = oldTab.parentNode;
		      parent.removeChild(oldTab);
		      parent.innerHTML = response.table;
		      stripe();
		  }
		  );
    }
}

