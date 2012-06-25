$(function(){
    resourceTypeArray = new Array();
    subItems = {};
    idUserRepository = "";
    getUserRepositoryId();

    var modal = createTreeDialog();

    $('#dialog_tree_button').click(function(){
        modal.modal("show");
    });

    $.ajax({
        type: 'POST',
        url: Routing.generate('claro_resource_type_resource', {
            'format':'json', 'listable':'true'
        }),
        success: function(data){
            var JSONObject = eval(data);
            var cpt = 0;

            while (cpt<JSONObject.length) {
                resourceTypeArray[cpt]=JSONObject[cpt];
                cpt++;
            }
            subItems = generateSubItems();
        },
        error: function(data){
            alert("resource type loading failed");
        }
    });


    $('#workspace_source_list_button').click(function(){
        appendRegisteredWorkspacesList('#source_tree');
    });

    $('#workspace_destination_list_button').click(function(){
        appendRegisteredWorkspacesList('#destination_tree');
    });

    $('.cfp_workspace_show_tree').live("click", function (event){
        var divId = event.target.parentElement.attributes[0].nodeValue;
        $('#'+divId).empty();
        var repositoryId = event.target.attributes[0].value;
        $('#'+divId).dynatree("destroy");
        document.getElementById(divId).setAttribute('data-workspaceId', repositoryId);
        createTree('#'+divId, repositoryId);
    });

    function createTree(treeId, repositoryId)
    {
        $(treeId).dynatree({
            title: "myTree",
            initAjax:{
                url:Routing.generate('claro_resource_node',{
                    'instanceId':0,
                    'workspaceId': repositoryId,
                    'format':'json'
                })
            },
            clickFolderMode: 1,
            onLazyRead: function(node){
                node.appendAjax({
                    url:Routing.generate('claro_resource_node', {
                        'instanceId':node.data.key,
                        'workspaceId': repositoryId,
                        'format': 'json'
                    })
                });
            },
            onCreate: function(node, span){
                bindContextMenu(node, repositoryId);
            },
            onDblClick: function(node)
            {
                node.expand();
                node.activate();
            },
            onCustomRender: function(node){
                var html = "<a class='dynatree-title' style='cursor:pointer;' href='#'> "+node.data.title+" share "+node.data.shareType+" </a>";
                html += "<span class='dynatree-custom-claro-menu' id='dynatree-custom-claro-menu-"+node.data.key+"' style='cursor:pointer; color:blue;'> menu </span>";
                return html;
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
                    if(node.tree == sourceNode.tree)
                    {
                        if (node.isDescendantOf(sourceNode)){
                            return false;
                        }
                        else {
                            sendRequest("claro_resource_move", {"idChild": sourceNode.data.key, "idParent": node.data.key});
                            sourceNode.move(node, hitMode);
                        }
                    }
                    else
                    {
                        if(sourceNode.data.shareType == 0)
                        {
                            alert("you can't share this resource");
                            return false;
                        }
                        var copynode;

                        if(sourceNode){
                            copynode = sourceNode.toDict(true, function(dict){
                               delete dict.key;
                            });
                        }

                        var html = getMoveFormHtml();
                        $('#ct_tree').hide();
                        $('#ct_form').append(html);
                        $('#ct_move_form_submit').click(function(event)
                        {
                            var destinationTreeId = node.tree.divTree.attributes[0].value;
                            var workspaceDestinationId = document.getElementById(destinationTreeId).getAttribute('data-workspaceId');

                            var option =  getCheckedValue(document.forms['ct_move_form']['options']);
                            if (option == 'move')
                            {
                                /* moving a node from a tree to another isn't implemented yet for dynatree,
                                 * so the node is copied then removed' */
                                sendRequest('claro_resource_move', {'idChild':sourceNode.data.key, 'idParent':node.data.key, 'workspaceDestinationId':workspaceDestinationId});
                                node.addChild(copynode);
                                sourceNode.remove();
                            }
                            else
                            {
                                sendRequest('claro_resource_add_workspace', {'instanceId':sourceNode.data.key, 'instanceDestinationId':node.data.key, 'options':option, 'workspaceId':workspaceDestinationId});
                                node.addChild(copynode);
                            }
                            $('#ct_form').empty();
                            $('#ct_form').hide();
                            $('#ct_tree').show();
                        });
                    }
                },
                onDragLeave: function(node, sourceNode){
                }
            }
        });
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

    function submissionHandler(xhr, route, routeParameters, node)
    {
        console.debug(node);
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
                    resourceId:instance.resourceId,
                }

            if (instance.type == 'directory')
            {
                newNode.isFolder = true;
            }

            if(node.data.key != newNode.key)
            {
                node.addChild(newNode);
            }
            else
            {
                node.data.title = newNode.title;
                node.data.shareType = newNode.shareType;
                node.render();
            }

            $('#ct_tree').show();
            $('#ct_form').empty();
        }
        else
        {
            $('#ct_form').empty();
            $('#ct_form').append(xhr.responseText);
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

    function successHandler()
    {
        alert("success");
    }

    function createFormDialog(type, id, node)
    {
        var route = Routing.generate('claro_resource_form', {
            'type':type,
            'instanceParentId':id
        });
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#ct_tree').hide();
                $('#ct_form').append(data);
                $("#generic_form").submit(function(e){
                    e.preventDefault();
                    sendForm("claro_resource_create",  {
                        'type':type,
                        'instanceParentId':id
                    }, document.getElementById("generic_form"), node);
                });
            }
        });
    }

    function deleteNode(node)
    {
        var repoId = document.getElementById('data-claroline').getAttribute('data-workspace_id');
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_resource_remove_workspace',{
                'resourceId':node.data.key,
                'workspaceId':repoId
            }),

            success: function(data){
                if(data == "success")
                {
                    node.remove();
                }
            }
        });
    }

    function openNode(node, repositoryId)
    {
        window.location = Routing.generate('claro_resource_open',{
            'workspaceId': repositoryId,
            'instanceId':node.data.key
        });
    }

    function viewNode(node)
    {
        var repoId = document.getElementById('data-claroline').getAttribute('data-workspace_id');
        window.location = Routing.generate('claro_resource_default_click',{
            'instanceId':node.data.key,
            'wsContextId':repoId
        });
    }

    function optionsNode(node)
    {
        var route = Routing.generate('claro_resource_options_form', {
            instanceId: node.data.key
        });
        $.ajax({
            type: 'POST',
            url: route,
            cache: false,
            success: function(data){
                $('#ct_tree').hide();
                $('#ct_form').append(data);
                $("#resource_options_form").submit(function(e){
                    e.preventDefault();
                    sendForm("claro_resource_edit_options",  {'instanceId': node.data.key}, document.getElementById("resource_options_form"), node);
                });
            }
        });
    }

    function bindContextMenu(node, repositoryId){
        var menuDefaultOptions = {
            selector: 'a.dynatree-title',
            callback: function(key, options) {
                switch(key)
                {
                    case "open":
                        openNode(node, repositoryId);
                        break;
                    case "delete":
                        deleteNode(node);
                        break;
                    case "view":
                        viewNode(node, key);
                        break;
                    case "options":
                        optionsNode(node);
                        break;
                    default:
                        node = $.ui.dynatree.getNode(this);
                        createFormDialog(key, node.data.key, node);
                        break;
                }
            },
            items: {
                "new": {
                    name: "new",
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
                "view": {
                    name: "view",
                    accesskey:"v"
                },
                "delete": {
                    name: 'delete',
                    accesskey:'d',
                    disabled: function(){
                        node = $.ui.dynatree.getNode(this);
                        if(node.data.key != 0)
                        {
                            return false;
                        }
                        else
                        {
                            return true;
                        }
                    }
                },
                "options":{
                    name: "options",
                    accesskey:'p'
                }
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

    function sendForm(route, routeParameters, form, node)
    {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', Routing.generate(route, routeParameters), true);
        xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
        xhr.onload = function(e){
            submissionHandler(xhr, route, routeParameters, node);
        };
        xhr.send(formData);
    }

    function generateSubItems()
    {
        var cpt = 0;
        var subItems='';
        subItems+='{'
        while (cpt<resourceTypeArray.length)
        {
            var name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            subItems+= '"'+resourceTypeArray[cpt].type+'": {"name":"'+translation+'"}';
            cpt++;
            if (cpt<resourceTypeArray.length) {
                subItems+=",";
            }
        }
        subItems+='}'
        object = JSON.parse(subItems);

        return object;
    }

    function createTreeDialog()
    {
        document.getElementById('ct_dialog').setAttribute("class", 'modal fade');
        var modalContent = ""
        +'<div class="modal-header">'
        +'<button id="close_dialog_button" class="close" data-dismiss="modal">×</button>'
        +'<h3> header</h3>'
        +'</div>'
        +'<div class="modal-body">'
        +"<div id='ct_form'></div></div><table id='ct_tree'><thead><tr><th><button id='workspace_source_list_button'>workspace source list</button></th>"
        +"<th><button id='workspace_destination_list_button'>workspace destination list</button></th></th></thead><tbody><tr valign='top'>"
        +"<td><div id='source_tree'></div></td><td><div id='destination_tree'></div></td></tr></tbody></table>"
        +'</div>'
        +'<div class="modal-footer">'
        +'FOOTER'
        +'</div>';

        $('#ct_dialog').append(modalContent);

        var modal = $('#ct_dialog').modal({
            show: false,
            backdrop: false
        });

        return modal;
    }

    function setSubItemsTranslations()
    {
        var cpt = 0;
        var name = "";
        while(cpt < resourceTypeArray.length) {
            name = resourceTypeArray[cpt].type;
            var translation = document.getElementById('translation-claroline').getAttribute('data-'+name);
            resourceTypeArray[cpt].type=translation;
            cpt++;
        }
    }

    function getUserRepositoryId()
    {
        $.ajax({
            type: 'POST',
            url: Routing.generate("claro_ws_user_workspace_id"),
            cache: false,
            success: function(data) {
                idUserRepository = data;
            },
            error: function(xhr) {
                alert(xhr.status);
            }
        });
    }

    function appendRegisteredWorkspacesList(tableSelector)
    {
        $(tableSelector).empty();

        var html="WORKSPACES : <br>";
        html += "<a class='cfp_workspace_show_tree' href='#' data-workspace_id="+idUserRepository+">"
        html += "local"
        html += "</a></br>";

        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_ws_list_user_workspaces', {
                format:'json'
            }),
            cache: false,
            success: function(data){
                JSONObject = JSON.parse(data);
                var cpt = 0;

                while (cpt<JSONObject.length) {
                    var name = JSONObject[cpt].name;
                    var id = JSONObject[cpt].id;
                    html +="<a class='cfp_workspace_show_tree' href='#' data-workspace_id="+id+">"
                    html += name
                    html +="</a></br>";
                    cpt++;
                }

                $(tableSelector).append(html);
            },
            error: function(xhr){
                alert(xhr.status);
            }
        });
    }

    function getMoveFormHtml()
    {
        var html = "";
        html+="<form id='ct_move_form'>"
        html+="<input type='radio' name='options' value='copy'>copy<br>"
        html+="<input type='radio' name='options' value='ref' checked>ref<br>"
        html+="<input type='radio' name='options' value='move'>move<br>"
        html+="<input type='submit' id='ct_move_form_submit'>"
        html+="</form>";

        return html;
    }

    function getCheckedValue(radioObj) {
        if(!radioObj)
            return "";
        var radioLength = radioObj.length;
        if(radioLength == undefined)
            if(radioObj.checked)
                return radioObj.value;
            else
                return "";
        for(var i = 0; i < radioLength; i++) {
            if(radioObj[i].checked) {
                return radioObj[i].value;
            }
        }
        return "";
    }

});
