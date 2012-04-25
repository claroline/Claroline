//dependency: dynatree & rod-contextMenu

resourceTypeArray = new Array();
subItems = {};

(function($){
    
    $.fn.extend({
        clarolineResourceTree: function(options){
            var defaultsDynatree = {
                title: "myTree",
                initAjax:{url:Routing.generate('claro_resource_JSON_node',{'id':0})},
                onLazyRead: function(node){
                    node.appendAjax({url:Routing.generate('claro_resource_JSON_node', {'id':node.data.key })});
                    bindContextMenu();
                },
                onCreate: function(node, span){
                    bindContextMenu();
                },
                dnd: {
                    onDragStart: function(node){
                        return true;
                    },
                    onDragStop: function(node){
                    },
                    autoExpandMS: 1000,
                    preventVoidMoves: true,
            
                    onDragEnter: function(node, sourceNode){
                        return true;
                    },
                    onDragOver: function(node, sourceNode, hitMode){
                        if(node.isDescendantOf(sourceNode)){
                            return false;
                        }
                    },
                    onDrop: function(node, sourceNode, hitMode, ui, draggable){
                        sendRequest("claro_resource_move", {"idChild": sourceNode.data.key, "idParent": node.data.key });
                        sourceNode.move(node, hitMode);
                    },
                    onDragLeave: function(node, sourceNode){
                    }  
                }
            }
            
            return this.each(function(){
                console.debug(this);
                
                $.ajax({
                type: 'POST',
                url: Routing.generate('claro_resource_type_resource'),
                success: function(data){
                    //JSON.parse doesn't work: why ?
                    var JSONObject = eval(data);
                    var cpt = 0;
                
                    while (cpt<JSONObject.length)
                    {
                        resourceTypeArray[cpt]=JSONObject[cpt];
                        cpt++;
                    }
                    subItems = generateSubItems();
                    }
                });
                
                
                $(this).dynatree(defaultsDynatree);

                return this;
            }); 
        }
        
    });
})(jQuery);

function generateSubItems()
{
    var cpt = 0;
    var subItems='';
    subItems+='{'
    while(cpt<resourceTypeArray.length)
    {
        subItems+= '"'+resourceTypeArray[cpt].type+'": {"name":"'+resourceTypeArray[cpt].type+'"}';                
        cpt++;
        if(cpt<resourceTypeArray.length)
        {
            subItems+=",";
        }       
    }    
    subItems+='}'
    object = JSON.parse(subItems);
    console.debug(object);
    
    return object;
}
    
function bindContextMenu(){
    
    console.debug(subItems);
    
    $.contextMenu({
    selector: 'span.dynatree-node', 
        callback: function(key, options) {
            //menu click events
            var m = "clicked: " + key;
            console.debug(m);
            switch(key)
            {
                case "open":
                    openNode(node);
                    break;
                case "delete":
                    deleteNode(node);
                    break;
                     
                case "view":
                    viewNode(node, key);
                    break;
                default:
                    node = $.ui.dynatree.getNode(this);
                    createFormDialog(key, node.data.key);    
            }
        },
    items: {
        "new": {name: "new",
            disabled: function(){
                node = $.ui.dynatree.getNode(this);
                if(node.data.isFolder == true)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            },
            items:subItems
        },
        "open": {
            name: "open",
            accesskey:"o",
            disabled: function(){
                node = $.ui.dynatree.getNode(this);
                if(node.data.isFolder != true)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
       },
       "view": {name: "view", accesskey:"v"},
       "delete": {name: "delete", icon: "delete", accesskey:"d"}
       }
   });   
}
    


