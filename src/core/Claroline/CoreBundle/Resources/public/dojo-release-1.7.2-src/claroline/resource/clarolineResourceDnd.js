define([
     "dojo/_base/declare",
     "dijit/tree/dndSource", 
 ],
 function(declare, dndSource){
     checkClaroItemAcceptance = function(node, source){     
        var newParent = dijit.getEnclosingWidget(node).item;
        var newChild = source.anchor.item;
        var droppable = true;
        //console.debug(source.anchor.item);
        if(newParent.type != 'directory')
        {
            droppable = false;
        }
        else
        {
            dojo.forEach(newParent.children, function(entry, i)
            {
                console.debug(entry.id+" at index "+i);
                if(entry.id == newChild.id)
                {
                    console.debug("return false");
                    droppable = false;
                }
            });  
        }
        return droppable;  
     };
     return declare('claroline/resource/clarolineResourceDnd', dndSource, {
        checkItemAcceptance: checkClaroItemAcceptance,
        singular: true
     });
 });

