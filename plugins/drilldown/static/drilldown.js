
Array.prototype.exists = function(o) {
    for(var i = 0; i < this.length; i++) {
	if(this[i] === o) {
	    return true;
	}
    }
    return false;
};

function freecmdbDrilldownStrip(haystack, needle)
{
    var pattern = new RegExp("^"+needle+"$","gm");
    return haystack.replace(pattern,"");
}

function freecmdbDrilldownItem(main, id, name)
{
    var select = document.createElement(drilldownIsEmbeded?'button':'a');

    main.appendChild(select);
    $(select).text(name);
    if (drilldownIsEmbeded) 
    {
	select.stat=false;
	//	select.type='button';
	select.onclick=function(event) {
	    select.state = !select.state;
	    var target = $('#'+drilldownUpdateTarget)[0];
	    if (target.type == 'hidden') {
		if(select.state) {
		    target.value += "" + id + "\n";	    
		    select.className = 'drilldown_selected';
		}
		else {
		    target.value = freecmdbDrilldownStrip(target.value, ""+id);
		    select.className = '';
		}
	    }
	    else {
		target.value += "" + id + " - " + name + "\n";	    
	    }
	    return false;
	};
		
    }
    else
    {
	select.href=drilldownBaseUrl+ id;
    }
}


function freecmdbDrilldownAdd(ci_id, node, skip)
{
    var ci_data = drilldownData[""+ci_id];
    
    function addChild(node, child_id) 
    {
	var child_data = drilldownData[""+child_id];

	var main = document.createElement('div');
	main.className = "drilldown_subtree";

	var has_good_children = false;

	for(var i=0; i<child_data.children.length; i++) 
	{
	    var grandchild_id = child_data.children[i];
	    if (skip.exists(""+grandchild_id) ||
		""+grandchild_id == ""+ci_id)
		continue;
	    
	    has_good_children=true;
	    break;
	}

	var expand = document.createElement('button');
	
	main.appendChild(expand);
	expand.innerHTML = "";
	expand.className="drilldown_expand expanded";
	//expand.type='button';
	expand.onclick=function() {return false;};

	if (has_good_children) {
	    expand.innerHTML = '+';
	    expand.className="drilldown_expand";
	    expand.onclick=function() {
		var my_skip = skip.slice(0);
		my_skip.push(""+ci_id);
		
		freecmdbDrilldownAdd(child_id, main, my_skip);
		expand.onclick = "";
		expand.innerHTML="";
		expand.className="drilldown_expand expanded";
		return false;
	    };
	}
	
	freecmdbDrilldownItem(main, child_id, child_data.name);
	node.appendChild(main);
    }

    for(var i=0; i<ci_data.children.length; i++) 
    {
	var child_id = ci_data.children[i];
	if (skip.exists(""+child_id))
	    continue;
	
	addChild(node, child_id);
    }

    /*
      Add orphans to root
    */
    if (skip.length === 0) {
	$.each(drilldownData, function(id, ci){
		if (ci.children.length === 0) {
		    addChild(node, ci.id);
		}
	    });
    }

}
