$(function(){
    jsonmenu = {};

    //Gets the menu lists.
    $.ajax({
        type: 'GET',
        url: Routing.generate('claro_json_menu', {'type': 'all'}),
        cache: false,
        success: function(data){
            jsonmenu = JSON.parse(data);
        },
        complete: function(){
            createDivTree();
            createTree('#source_tree');
        }
    });

    /**
     * Creates the divisions needed for the file manager.
     * source_tree => workspaces and their content.
     * ct_form => the file manager widget
     * ct_dialog is the div containing the file manager
     * workspace_settins is a test
     */
    function createDivTree()
    {
        var content = ""
        +"<div id='ct_form'></div><br>"
        +"<div id='source_tree'></div>"
        $('#ct_dialog').append(content)
    }

    /**
     * Creates the resource tree
     *
     * @param {string} treeId the div id
     */
    function createTree(treeId)
    {
        $(treeId).dynatree({
            title: 'myTree',
            initAjax:{
                url:Routing.generate('claro_resource_root_node',{
                    'format':'json'
                })
            },
            clickFolderMode: 1,
            onLazyRead: function(node){
                node.appendAjax({
                    url:Routing.generate('claro_resource_node', {
                        'instanceId':node.data.key,
                        'workspaceId':node.data.workspaceId,
                        'format': 'json'
                    })
                });
            },
            onCreate: function(node, span){
                if(node.data.hasOwnProperty('type')){
                    bindContextMenuTree(node);
                }
            },
            onDblClick: function(node)
            {
                node.expand();
                node.activate();
            },
            onCustomRender: function(node){
                var html = "<a id='node_"+node.data.key+"' class='dynatree-title' style='cursor:pointer;' href='#'> "+node.data.title+" share "+node.data.shareType+" </a>";
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
                    if (node.isDescendantOf(sourceNode)){
                        return false;
                    }
                    else {
                        dropNode(node, sourceNode, hitMode);
                        //sendRequest('claro_resource_move', {'idChild': sourceNode.data.key, 'idParent': node.data.key, 'workspaceDestinationId':node.data.workspaceId});
                        //sourceNode.move(node, hitMode);
                    }
                },
                onDragLeave: function(node, sourceNode){
                }
            }
        });
    }

    function dropNode(node, sourceNode, hitMode)
    {
        var html = Twig.render(move_resource_form);
        $('#ct_form').empty();
        $('#ct_form').append(html);
        $('#move_resource_form_submit').click(function(e) {
            e.preventDefault();
            var option = getCheckedValue(document.forms['move_resource_form']['options']);
            if('move' == option){
                sendRequest('claro_resource_move', {'idChild': sourceNode.data.key, 'idParent': node.data.key, 'workspaceDestinationId':node.data.workspaceId});
                sourceNode.move(node, hitMode);
                $('#ct_form').empty();
            } else
            {
                sendRequest('claro_resource_add_workspace',
                            {'instanceId':sourceNode.data.key,'instanceDestinationId':node.data.key,'options':option}
                );

                var newNode = {
                        title:sourceNode.data.title,
                        key:sourceNode.data.key,
                        copy:sourceNode.data.copy,
                        instanceCount:sourceNode.data.instanceCount,
                        shareType:sourceNode.data.shareType,
                        resourceId:sourceNode.data.resourceId
                    }

                node.addChild(newNode);
                $('#ct_form').empty();
            }
        });
    }

    /**
     * Sends a standard ajaxRequest.
     *
     * @param {string}   route
     * @param {Object}   routeParams
     * @param {function} successHandler
     */
    function sendRequest(route, routeParams, successHandler)
    {
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

    /**
     * Sends a form to the backend for a certain node.
     * See SumbmissionHandler.
     */
    function sendForm(url, form, node)
    {
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
        xhr.onload = function(e){
            submissionHandler(xhr, node);
        };
        xhr.send(formData);
    }

    /**
     * The widget form submission handler.
     *
     * @param {Object} xhr
     * @param {Object} node
     */
    function submissionHandler(xhr, node)
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
                node.appendAjax({url:Routing.generate('claro_resource_node', {
                    'instanceId':node.data.key,
                    'workspaceId': document.getElementById(node.tree.divTree.attributes[0].value).getAttribute('data-workspaceId'),
                    'format': 'json'})
                });
                node.expand();
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
            $('#ct_form').find('form').submit(function(e){
                e.preventDefault();
                var action = $('#ct_form').find('form').attr('action');
                action = action.replace('_instanceId', node.data.key);
                action = action.replace('_resourceId', node.data.resourceId);
                var id = $('#ct_form').find('form').attr('id');
                sendForm(action, document.getElementById(id), node);
            })
        }
    }

    /**
     * Executes the desired action for a menu item.
     *
     * @param {Object} obj
     * @param {Object} node
     */
    function executeMenuActions(obj, node)
    {
        var executeAsync = function (obj, node, route) {
            var removeNode = function (node, route) {
                $.ajax({
                type: 'POST',
                url: route,
                success: function(data){
                    if(data == 'success'){
                    node.remove();
                        }
                    }
                });
            }

            var executeRequest = function (node, route) {
               $.ajax({
                    type: 'POST',
                    url: route,
                    cache: false,
                    success: function(data){
                        $('#ct_tree').hide();
                        $('#ct_form').append(data);
                        $('#ct_form').find('form').submit(function(e){
                            e.preventDefault();
                            var action = $('#ct_form').find('form').attr('action');
                            action = action.replace('_instanceId', node.data.key);
                            var id = $('#ct_form').find('form').attr('id');
                            sendForm(action, document.getElementById(id), node);
                        })
                    }
                });
            }

            switch(obj.name)
            {
                case "delete": removeNode(node, route); break;
                default: executeRequest (node, route); break;
            }

        }

        var route = obj.route;
        var compiledRoute = route.replace('_instanceId', node.data.key);
        compiledRoute = compiledRoute.replace('_resourceId', node.data.resourceId);

        (obj.async) ? executeAsync(obj, node, compiledRoute): window.location = compiledRoute;
    }

    /**
     * Finds wich menu object was clicked on in the menu description.
     *
     * @param {Object} items the menu description.
     * @param {Object} node the target node.
     * @param {string} menuItem the menuItem name.
     */
    function findMenuObject(items, node, menuItem)
    {
        for (var property in items.items){
            if(property == menuItem){
                executeMenuActions(items.items[property], node);
            } else {
                if (items.items[property].hasOwnProperty('items')){
                    findMenuObject(items.items[property], node, menuItem);
                }
            }
        }
    }

    /**
     * Creates the context menu for a specific node
     */
    function bindContextMenuTree(node)
    {
        var type = node.data.type;

        var menuDefaultOptions =
            {
                selector: '#node_'+node.data.key,
                callback: function(key, options){
                    findMenuObject(jsonmenu[type], node, key);
                }
            }

        menuDefaultOptions.items = jsonmenu[type].items;
        $.contextMenu(menuDefaultOptions);
        var additionalMenuOptions = $.extend(
            menuDefaultOptions,
            {selector: '#dynatree-custom-claro-menu-'+node.data.key, trigger: 'left'}
        );

        $.contextMenu(additionalMenuOptions);
    }

    /**
     * Return the check value of a combobox form.
     */
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
