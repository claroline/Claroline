/*
 * dependencies: dynatree & rod-contextMenu + jquery
 */
(function($){
    var resourceTypeArray = new Array();
    subItems = {};
    getResourceTypeJSON();
    getUserRepositoryId();
    var workspaceClickedId = "";

    $.fn.extend({
        claroFilePicker:function(options){
        var params = $.extend({
            autoOpen: false,
            backdrop: false,
            currentWorspaceId: null,
           // leftClickMenu: true,
            getForm: function(){
                var formHTML =
                    "<form id='cfp_form'><input id='resource_name_value' type='text' placeholder='"+"name"+"'/><br>"
                    formHTML += "<input type='radio' name='options' value='copy'>copy<br>"
                    formHTML += "<input type='radio' name='options' value='ref' checked>ref<br>"
                    formHTML +="<input type='submit' id='cfp_form_submit'></form>";
                return formHTML;
            },
            onSubmit: function(form){
                $('#cfp_form').hide();
                $('#cfp_tree').show();
                this.submitHandler(form, document.getElementById('resource_name_value').getAttribute('data-instanceId'));
            },
            dblClickItem: function(node){
                $('#cfp_tree').hide();
                $('#cfp_form').show();
                document.getElementById('resource_name_value').value=node.data.title;
                document.getElementById('resource_name_value').setAttribute('data-instanceId', node.data.key);
                document.getElementById('resource_name_value').setAttribute('readonly', 'readonly');
            },
            submitHandler: function(form, instanceId){
                alert("DEFAULT SUBMIT HANDLER! IT MUST BE CHANGED");
            }
        }, options);
        return this.each(function(){
            //must be changed with sthg like $(this).setAttribute.doSthg
            document.getElementById('filepicker').setAttribute("class", 'modal fade');
            var modalContent = ""
            +'<div class="modal-header">'
                +'<button id="close_dialog_button" class="close" data-dismiss="modal">×</button>'
                +'<h3>Modal header</h3>'
           +'</div>'
           +'<div class="modal-body">'
                +'<div id="cfp_dialog"></div>'
                +'<div id="cfp_top_bar"><button id="local_tree_button">local</button><button id="workspace_tree_button">workspace</button><button>others</button></div><br>'
                +'<div id="cfp_content"><div id="cfp_data"></div><div id="cfp_tree"></div><div id="cfp_form"></div></div>'
            +'</div>'
           +'<div class="modal-footer">'
               +'FOOTER'
            +'</div>'
            +'</div>';
            $(this).append(modalContent);

            $('#cfp_form').append(params.getForm());
            $('#cfp_form').hide();
            $('#cfp_form').submit(function(e){
                e.preventDefault();
                params.onSubmit(document.forms['cfp_form']);
            });
            //create a new file tree
            var defaultsDynatree = {
                title: "myTree",
                initAjax:{url:Routing.generate('claro_resource_node',{'instanceId':0, 'workspaceId': document.getElementById("local_tree_button").getAttribute("data-userRepositoryId"), 'format': 'json'})},
                clickFolderMode: 1,
                onLazyRead: function(node){
                    node.appendAjax({url:Routing.generate('claro_resource_node', {'instanceId':node.data.key, 'workspaceId': workspaceClickedId, 'format': 'json'})});
                },
                onCreate: function(node, span){
                    bindContextMenu(node);
                },
                onDblClick: function(node){
                    $('#cfp_dialog').hide();
                    if(workspaceClickedId == params.currentWorkspaceId)
                    {
                        alert("you can't a resource this workspace in the current workspace");
                    }
                    else
                    {
                        if(node.data.shareType == 1)
                        {
                            params.dblClickItem(node);
                        }
                        else
                        {
                            alert("you can't share this resource");
                        }
                    }

                },
                onCustomRender: function(node){
                    var copy = node.data.copy;
                    var html ='';

                    if(copy == 1)
                    {
                        html += "<a class='dynatree-title' style='cursor:pointer; color:red' href='#'> "+node.data.title+" instance amount "+node.data.instanceCount+" share "+node.data.shareType+" </a>";
                        html += "<span class='dynatree-custom-claro-menu' id='dynatree-custom-claro-menu-"+node.data.key+"' style='cursor:pointer; color:blue;'> menu </span>";
                    }
                    else
                    {
                        html += "<a class='dynatree-title' style='cursor:pointer; color:green' href='#'> "+node.data.title+" instance amount "+node.data.instanceCount+" share "+node.data.shareType+" </a>";
                        html += "<span class='dynatree-custom-claro-menu' id='dynatree-custom-claro-menu-"+node.data.key+"' style='cursor:pointer; color:blue;'> menu </span>";
                    }
                    return html;
                },
                dnd: {
                    onDragStart: function(node){
                        return true;
                    },
                    onDragStop: function(node, sourceNode){
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
                        if(node.isDescendantOf(sourceNode)){
                            return false;
                        }
                        else{
                        sendRequest("claro_resource_move", {"idChild": sourceNode.data.key, "idParent": node.data.key});
                        sourceNode.move(node, hitMode);
                        }
                    },
                    onDragLeave: function(node, sourceNode){
                    }
                }
            }

            $('#local_tree_button').live("click", function(event){
                $('#cfp_tree').dynatree("destroy");
                $('#cfp_tree').empty();
                workspaceClickedId = document.getElementById("local_tree_button").getAttribute("data-userRepositoryId");
                var customWorkspaceDynatree =
                   $.extend(defaultsDynatree,
                       {
                           initAjax:{url:Routing.generate('claro_resource_node',{'instanceId':0, 'workspaceId': document.getElementById("local_tree_button").getAttribute("data-userRepositoryId"), 'format':'json'})},
                           onCreate: function(node, span){
                               bindContextMenu(node);
                           }
                       });
                $('#cfp_tree').dynatree(customWorkspaceDynatree);
                $('#cfp_form').hide();
                $('#cfp_dialog').hide();
                $('#cfp_data').hide();
                $('#cfp_tree').show();
            });

            $('#workspace_tree_button').click(function(){
                appendRegisteredWorkspacesList();
                $('#cfp_form').hide();
                $('#cfp_tree').hide();
                $('#cfp_dialog').hide();
                $('#cfp_data').show();
            });

            $('.cfp_workspace_show_tree').live("click", function (event){
                $('#cfp_tree').dynatree("destroy");
                $('#cfp_tree').empty();
                var idRepository = event.target.attributes[0].value;
                workspaceClickedId = idRepository;
                var customWorkspaceDynatree =
                    $.extend(defaultsDynatree,
                        {
                            initAjax:{url:Routing.generate('claro_resource_node',{'instanceId':0, 'workspaceId': idRepository, 'format': 'json'})},
                            onCreate: function(node, span){
                               bindContextMenu(node);
                            }
                        });
                $('#cfp_tree').dynatree(customWorkspaceDynatree);
                $('#cfp_form').hide();
                $('#cfp_data').hide();
                $('#cfp_dialog').hide();
                $('#cfp_tree').show();
            });

            $('#close_dialog_button').click(function(){});
            $(this).modal({
                show:params.autoOpen,
                backdrop:params.backdrop
            });

            return (this);

        });}
    });

    function getUserRepositoryId()
    {
        $.ajax({
            type: 'POST',
            url: Routing.generate("claro_ws_user_workspace_id"),
            cache: false,
            success: function(data){
                document.getElementById('local_tree_button').setAttribute('data-userRepositoryId', data);
            },
            error: function(xhr){
                alert(xhr.status);
            }
        });
    }

    function generateSubItems()
    {
        var cpt = 0;
        var subItems='';
        subItems+='{'
        while(cpt<resourceTypeArray.length)
        {
            var name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            subItems+= '"'+resourceTypeArray[cpt].type+'": {"name":"'+translation+'"}';
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

    function bindContextMenu(node){
        var menuDefaultOptions = {
        selector: 'a.dynatree-title',
            callback: function(key, options) {
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
                    case "edit":
                        editNode(node, key);
                        break;
                    case "options":
                        optionsNode(node);
                        break;
                    default:
                        node = $.ui.dynatree.getNode(this);
                        createFormDialog(key, node.data.key, node);
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
        "edit": {name: "edit", accesskey:"e"},
        "delete": {
            name: "delete",
            accesskey:"d",
            disabled: function(){
                    node = $.ui.dynatree.getNode(this);
                    if(node.data.key != 0){
                        return false;
                        }
                        else
                        {
                            return true;
                        }
                    }
            },
         "options": {name: "options", accesskey:'p'}
        }
    }
        $.contextMenu(menuDefaultOptions);


        var additionalMenuOptions = $.extend(menuDefaultOptions,
        {
            selector: 'span.dynatree-custom-claro-menu',
            trigger: 'left'
        });

        $.contextMenu(additionalMenuOptions);
    }

    function getResourceTypeJSON()
    {
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_resource_type_resource', {'format':'json', 'listable':'true'}),
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
                },
                error: function(xhr){
                alert("resource type loading failed");
                }
        });

    }

    function deleteNode(node)
    {
        $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_remove_workspace',{'resourceId':node.data.key, 'workspaceId':workspaceClickedId}),
        success: function(data){
            if(data == "success")
            {
                node.remove();
            }
        }
        });
    }

    function editNode(node)
    {
        alert("this will create a copy")
        alert('clickedWorkspace = '+workspaceClickedId);

        $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_edit',{'instanceId':node.data.key, 'workspaceId': workspaceClickedId, 'options':'copy'}),
        success: function(data){
            if(data=="edit")
            {
                alert("this was edited");
            }
        }
        });
    }

    function openNode(node)
    {
        window.location = Routing.generate('claro_resource_open',{'workspaceId': workspaceClickedId, 'id':node.data.key});
    }

    function viewNode(node)
    {
        window.location = Routing.generate('claro_resource_default_click',{'instanceId':node.data.key, 'wsContextId':workspaceClickedId});
    }

    function optionsNode(node){
        var route = Routing.generate('claro_resource_options_form', {
            instanceId: node.data.key
        });
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#cfp_dialog').empty();
                $('#cfp_dialog').append(data);
                $('#cfp_dialog').show();
                $("#resource_options_form").submit(function(e){
                    e.preventDefault();
                    sendForm("claro_resource_edit_options",  {'instanceId': node.data.key}, document.getElementById("resource_options_form"), node);
                    });
                }
            });
    }

    function createFormDialog(type, id, node){
        var route = Routing.generate('claro_resource_form', {'type':type, 'instanceParentId':id});
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#cfp_dialog').empty();
                $('#cfp_dialog').append(data);
                $('#cfp_dialog').show();
                $("#generic_form").submit(function(e){
                    e.preventDefault();
                    sendForm("claro_resource_create",  {'type':type, 'instanceParentId':id, 'workspaceId':workspaceClickedId}, document.getElementById("generic_form"), node);
                    });
                }
            });
    }

    function submissionHandler(xhr, route, routeParameters, node)
    {
        if(xhr.getResponseHeader('Content-Type') == 'application/json')
        {
            var JSONObject = JSON.parse(xhr.responseText);
            var instance = JSONObject[0];

            var newNode = {
                    title:instance.title,
                    key:instance.key,
                    copy:instance.copy,
                    instanceCount:instance.instanceCount,
                    shareType:instance.shareType,
                    resourceId:instance.resourceId
                }

            if (instance.type == 'directory')
            {
                newNode.isFolder = true;
            }

            if(node.data.key != newNode.key)
            {
                node.appendAjax({url:Routing.generate('claro_resource_node', {'instanceId':node.data.key, 'workspaceId': workspaceClickedId, 'format': 'json'})});
                node.expand();
            }
            else
            {
                node.data.title = newNode.title;
                node.data.shareType = newNode.shareType;
                node.render();
            }

            $('#cfp_dialog').empty();
        }
        else
        {
            $('#cfp_dialog').empty();
            $('#cfp_dialog').append(xhr.responseText);
            $("#generic_form").submit(function(e){
                e.preventDefault();
                sendForm(route, routeParameters, document.getElementById("generic_form"), node);
                });
            $("#resource_options_form").submit(function(e){
                e.preventDefault();
                sendForm("claro_resource_edit_options",  {'instanceId': node.data.key}, document.getElementById("resource_options_form"), node);
            });
        }
    }

    function sendForm(route, routeParameters, form, node)
    {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', Routing.generate(route, routeParameters), true);
        xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
        xhr.onload = function(e){submissionHandler(xhr, route, routeParameters, node)};
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

    function appendRegisteredWorkspacesList()
    {
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_ws_list_user_workspaces', {format:'json'}),
            cache: false,
            success: function(data){

                $('#cfp_data').empty();
                JSONObject = JSON.parse(data);
                var html="WORKSPACES : <br>";
                var cpt = 0;

                while (cpt<JSONObject.length)
                {
                    var name = JSONObject[cpt].name;
                    var id = JSONObject[cpt].id;
                    html +="<a class='cfp_workspace_show_tree' href='#' data-workspace_id="+id+">"
                    html += name
                    html +="</a></br>";
                    cpt++;
                }
                    $('#cfp_data').append(html);
                },
                error: function(xhr){
                    alert(xhr.status);
                }
        });
    }

    function successHandler(){
        alert("successHANDLER");
    }

})(jQuery);
