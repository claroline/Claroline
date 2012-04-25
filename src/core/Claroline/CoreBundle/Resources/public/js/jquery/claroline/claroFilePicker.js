/*
 * dependencies: dynatree & rod-contextMenu + jquery
 */

resourceTypeArray = new Array();
subItems = {};
getResourceTypeJSON();

(function($){
    $.fn.extend({
        claroFilePicker:function(options){
        var params = $.extend({
            autoOpen: true,
            resizable: false,
            width: 500,
            height: 350,
            getForm: function(){
                var formHTML =
                    "<form id='cfp_form'><input id='resource_name_value' type='text' placeholder='"+"ahah"+"'/><input type='submit' id='cfp_form_submit'></form>";
                return formHTML;
            },
            onSubmit: function(form){
                $('#cfp_form').hide();
                $('#cfp_tree').show();
                this.submitHandler(form, document.getElementById('resource_name_value').getAttribute('data-id'));
            },
            dblClickItem: function(node){
                $('#cfp_tree').hide();
                $('#cfp_form').show();
                document.getElementById('resource_name_value').value=node.data.title;
                document.getElementById('resource_name_value').setAttribute('data-id', node.data.key);
                document.getElementById('resource_name_value').setAttribute('readonly', 'readonly');
            },
            submitHandler: function(form, resourceId){
                alert("DEFAULT SUBMIT HANDLER! IT MUST BE CHANGED");
            }
        }, options);          
        return this.each(function(){   
            var divTopHTML = "<div id='cfp_top_bar'><button id='local_tree_button'>local</button><button>others</button></div>"
            $(this).append(divTopHTML);
            var divContentHTML = "<div id='cfp_content'><div id='cfp_tree'></div><div id='cfp_form'></div></div>";
            $(this).append(divContentHTML);
            var divDialogHTML = "<div id='cfp_dialog'></div>";
            $(this).append(divDialogHTML);
            
            $('#cfp_form').append(params.getForm());
            $('#cfp_form').hide();
            $('#cfp_form').submit(function(e){
                e.preventDefault();
                params.onSubmit(document.forms['cfp_form']);
            }); 
            //create a new file tree
            var defaultsDynatree = {
                title: "myTree",
                initAjax:{url:Routing.generate('claro_resource_JSON_node',{'id':0})},
                clickFolderMode: 1,
                onLazyRead: function(node){
                    node.appendAjax({url:Routing.generate('claro_resource_JSON_node', {'id':node.data.key})});
                    bindContextMenu();
                },
                onCreate: function(node, span){
                    bindContextMenu();
                },
                onDblClick: function(node){
                    params.dblClickItem(node);
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
                        sendRequest("claro_resource_move", {"idChild": sourceNode.data.key, "idParent": node.data.key});
                        sourceNode.move(node, hitMode);
                    },
                    onDragLeave: function(node, sourceNode){
                    }  
                }
            }
            
            $('#local_tree_button').click(function(){            
                $('#cfp_tree').dynatree(defaultsDynatree); 
                $('#cfp_form').hide();
                $('#cfp_tree').show();
            });
            
            console.debug(this); 
            $(this).dialog({
                autoOpen: params.autoOpen,
                resizable: params.resizable,
                width: params.width,
                height: params.height
            });  
            return this; 
            
        });}
    });
})(jQuery);

//pas terrible la création... une manière plus propre de le faire en js ?
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

function getResourceTypeJSON()
{
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
}

function deleteNode(node)
{
    $.ajax({
    type: 'POST',
    url: Routing.generate('claro_resource_delete',{'id':node.data.key}),
    success: function(data){
        if(data=="delete")
        {
            node.remove();
        }       
    }
    });
}
    
function openNode(node)
{
    window.location = Routing.generate('claro_resource_open',{'id':node.data.key});
}   
    
function viewNode(node)
{
    window.location = Routing.generate('claro_resource_default_click',{'id':node.data.key});
}

function createFormDialog(type, id){
        route = Routing.generate('claro_resource_form_resource', {'type':type, 'id':id});
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#cfp_dialog').append(data);
                $("#cfp_dialog").dialog('open');
                //ici je change l'event du submit
                $("#generic_form").submit(function(e){
                    e.preventDefault();
                    sendForm("claro_resource_add_resource",  {'type':type, 'id':id}, document.getElementById("generic_form"));
                    });
                }
            });
}

//todo:submission: check si la réponse est du json
function submissionHandler(data, route, routeParameters)
{
    if(data!="epic fail")
    {
        var JSONObject = JSON.parse(data);
        var node = $("#cfp_tree").dynatree("getTree").selectKey(routeParameters.id);
        if(JSONObject.type != 'directory')
        {
            var childNode = node.addChild({
                title:JSONObject.name,
                key:JSONObject.key
            });
        }
        else
        {
            var childNode = node.addChild({
                title:JSONObject.name,
                key:JSONObject.key,
                isFolder:true
            });
        }
        $('#cfp_dialog').dialog("close");
        $('#cfp_dialog').empty();
    }
    else
    {
        $('#cfp_dialog').append(data);
        $("#generic_form").submit(function(e){
            e.preventDefault();
            sendForm(route, routeParameters, document.getElementById("generic_form"));
            });
    }
}

function sendForm(route, routeParameters, form)
{
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', Routing.generate(route, routeParameters), true);
    xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
    xhr.onload = function(e){submissionHandler(xhr.responseText, route, routeParameters)};
    xhr.send(formData);
}

function sendRequest(route, routeParams, successHandler){
    $.ajax({
        type: 'POST',
        url: Routing.generate(route, routeParams),
        cache: false,
        success: successHandler,
        error: function(xhr){
            alert(xhr.status);
        }
    });
}

function successHandler(){
    alert("success");
}