
$(function(){
    var wsjsonmenu = {};
    var sourceId = 0;
    var params = {
        mode: 'picker',
        resourcePickedHandler: function(id){
            $('#bootstrap-modal').modal("hide");
            var route = {
                'name': 'claro_resource_add_workspace',
                'parameters': {
                    'instanceId': id,
                    'instanceDestinationId': sourceId,
                    'options': 'ref'
                }
            }
            ClaroUtils.sendRequest(route, function(data){
                var node = $('#ws_tree').dynatree('getTree').selectKey(sourceId);
                node.appendAjax({
                    url: Routing.generate('claro_resource_children', {
                        'instanceId': sourceId
                    }),
                    error: function (node, XMLHttpRequest, textStatus, errorThrown) {
                        if (XMLHttpRequest.status == 403) {
                            ClaroUtils.ajaxAuthenticationErrorHandler(function () {
                                window.location.reload();
                            });
                        } else {
                            alert('this node could not be loaded');
                        }
                    }
                });
                alert(data);
            });
        }
    };

    picker = $('#modal-body').claroResourceManager(params)
    createDivTree($('#currentWorkspaceTree'));

    $.ajax({
        type: 'GET',
        url: Routing.generate('claro_resource_menus'),
        cache: false,
        success: function (data) {
            wsjsonmenu = JSON.parse(data);
            wsjsonmenu.directory.items.add = {'name': 'add'};
        },
        complete: function () {
            createTree('#ws_tree');
        }
    });

    function createDivTree(div) {
        var content = ""
        +"<div id='ws_form'></div><br>"
        +"<div id='ws_tree'>CONTINENT</div>";
        div.append(content);
    }

    function createTree(treeId)
    {
        $(treeId).dynatree({
            title: 'myTree',
            initAjax:{
                url: Routing.generate('claro_resource_root', {'workspaceId': document.getElementById('jsdata').getAttribute('data-ws-id')})
            },
            clickFolderMode: 1,
            onLazyRead: function (node) {
                node.appendAjax({
                    url: Routing.generate('claro_resource_children', {
                        'instanceId': node.data.key
                    }),
                    error: function (node, XMLHttpRequest, textStatus, errorThrown) {
                        if (XMLHttpRequest.status == 403) {
                            ClaroUtils.ajaxAuthenticationErrorHandler(function () {
                                window.location.reload();
                            });
                        } else {
                            alert('this node could not be loaded');
                        }
                    }
                });
            },
            onCreate: function (node, span) {
                if (node.data.hasOwnProperty('type')) {
                    if(undefined != jsonmenu[node.data.type]){
                        bindContextMenuTree(node);
                    }
                }
            },
            onDblClick: function (node) {
                    node.expand();
                    node.activate();
            },
            onCustomRender: function (node) {
                var html = "<a id='ws_node_"+node.data.key+"' class='ws_dynatree-title' style='cursor:pointer;' href='#'> "+node.data.title+" share "+node.data.shareType+" </a>";
                html += "<span class='ws_dynatree-custom-claro-menu' id='ws_dynatree-custom-claro-menu-"+node.data.key+"' style='cursor:pointer; color:blue;'> menu </span>";
                return html;
            },
            dnd: {
                onDragStart: function (node) {
                    return true;
                },
                onDragStop: function (node) {
                },
                autoExpandMS: 1000,
                preventVoidMoves: true,
                onDragEnter: function (node, sourceNode) {
                    return true;
                },
                onDragOver: function (node, sourceNode, hitMode) {
                    if (node.isDescendantOf(sourceNode)) {
                        return false;
                    }
                },
                onDrop: function (node, sourceNode, hitMode, ui, draggable) {
                    if (node.isDescendantOf(sourceNode)) {
                        return false;
                    }
                    else {
                        dropNode(node, sourceNode, hitMode);
                    }
                }
            }
        });

        function dropNode(node, sourceNode, hitMode)
        {
            sourceNode.move(node, hitMode);
        }

        var bindContextMenuTree = function(node)
        {
            var type = node.data.type;
            var menuDefaultOptions = {
                selector: '#ws_node_'+ node.data.key,
                callback: function (key, options) {
                    switch(key)
                    {
                        case 'add':addToWsTree(key, options, node);break;
                        default:findMenuObject(wsjsonmenu[type], node, key);break;
                    }
                }
            }

            menuDefaultOptions.items = wsjsonmenu[type].items;
            $.contextMenu(menuDefaultOptions);
            var additionalMenuOptions = $.extend(menuDefaultOptions, {
                selector: '#ws_dynatree-custom-claro-menu-' + node.data.key,
                trigger: 'left'
            });

            $.contextMenu(additionalMenuOptions);

            function addToWsTree(key, option, node){
                $('#bootstrap-modal').modal("show");
                sourceId = node.data.key;
            }

            function executeMenuActions(obj, node)
            {
                console.debug(obj);
                var submissionHandler = function(xhr){
                    if (xhr.getResponseHeader('Content-Type') == 'application/json') {
                        var JSONObject = JSON.parse(xhr.responseText);
                        var instance = JSONObject[0];
                        var newNode = {
                            title: instance.title,
                            key: instance.key,
                            copy: instance.copy,
                            instanceCount: instance.instanceCount,
                            shareType: instance.shareType,
                            resourceId: instance.resourceId
                        }

                        if (instance.type == 'directory') {
                            newNode.isFolder = true;
                        }

                        if (node.data.key != newNode.key) {
                            node.appendAjax({
                                url:Routing.generate('claro_resource_children', {
                                    'instanceId':node.data.key
                                })
                            });
                            node.expand();
                        } else {
                            node.data.title = newNode.title;
                            node.data.shareType = newNode.shareType;
                            node.render();
                        }

                        $('#ws_tree').show();
                        $('#ws_form').empty();
                    } else {
                        $('#ws_form').empty();
                        $('#ws_form').append(xhr.responseText);
                        $('#ws_form').find('form').submit(function (e) {
                            e.preventDefault();
                            var action = $('#ws_form').find('form').attr('action');
                            action = action.replace('_instanceId', node.data.key);
                            action = action.replace('_resourceId', node.data.resourceId);
                            var id = $('#ws_form').find('form').attr('id');
                            ClaroUtils.sendForm(action, document.getElementById(id), submissionHandler);
                        });
                    }
                }

                var executeAsync = function (obj, node, route) {
                    var removeNode = function () {
                        ClaroUtils.sendRequest(route, function (data, textStatus, jqXHR) {
                            if (204 == jqXHR.status) {
                                node.remove();
                            }
                        });
                    }

                    var executeRequest = function () {
                        ClaroUtils.sendRequest(route, function (data) {
                            $('#ws_form').append(data);
                            $('#ws_form').find('form').submit(function (e) {
                                e.preventDefault();
                                var action = $('#ws_form').find('form').attr('action');
                                action = action.replace('_instanceId', node.data.key);
                                var id = $('ws_form').find('form').attr('id');
                                ClaroUtils.sendForm(action, document.getElementById(id), submissionHandler);
                            });
                        })
                    }

                    switch (obj.name) {
                        case 'delete':
                            removeNode(node, route);
                            break;
                        default:
                            executeRequest (node, route);
                            break;
                    }
                }

                var route = obj.route;
                var compiledRoute = route.replace('_instanceId', node.data.key);
                compiledRoute = compiledRoute.replace('_resourceId', node.data.resourceId);
                obj.async ? executeAsync(obj, node, compiledRoute): window.location = compiledRoute;
            }

            var findMenuObject = function(items, node, menuItem)
            {
                for (var property in items.items) {
                    if (property == menuItem) {
                        executeMenuActions(items.items[property], node);
                    } else {
                        if (items.items[property].hasOwnProperty('items')) {
                            findMenuObject(items.items[property], node, menuItem);
                        }
                    }
                }
            }
        }
    }
});



