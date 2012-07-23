
$(function(){
    var wsjsonmenu = {};
    var sourceId = 0;
    var displayMode = 'classic';
    var rootws = {};
    var rootTypes = [];
    var children = {};
    var params = {
        mode: 'picker',
        resourcePickedHandler: function(id){
            $('#bootstrap-modal').modal("hide");
            var route = {
                'name': 'claro_resource_add_workspace',
                'parameters': {
                    'resourceId': id,
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
                            console.debug(XMLHttpRequest);
                            alert('this node could not be loaded');
                        }
                    }
                });
            });
        }
    };

    $('#modal-body').claroResourceManager(params)
    createDivTree($('#currentWorkspaceTree'));

    function createDivTree(div) {
        var content = ""
        +"<div id='ws_form'></div><br>"
        +"<div id='ws_mode'><button id='ws_switch_mode'>switch mode</button></div><br>"
        +"<div id='ws_tree'></div>";
        div.append(content);
        $('#ws_switch_mode').click(function(){
            if (displayMode == 'classic'){
               displayMode = 'linker';
               children = rootTypes;
            } else {
               displayMode = 'classic';
               children = rootws;
            }
            $('#ws_tree').dynatree('destroy').empty();
            createTree(children)
        });
    }

    ClaroUtils.sendRequest(
        Routing.generate('claro_resource_menus'),
        function(data){
            wsjsonmenu = JSON.parse(data);
            wsjsonmenu.directory.items.add = {
                'name': 'add'
            };
        },
        function(){
            ClaroUtils.sendRequest(Routing.generate('claro_resource_root', {
                'workspaceId': document.getElementById('jsdata').getAttribute('data-ws-id')
                }),
            function(data){
                initLinker();
                createTree(children)
                })
        })

    var initAjaxUrl = function (displayMode){
        var url = '';
        (displayMode == 'classic') ? url = Routing.generate('claro_resource_roots') : url = Routing.generate('claro_resource_children', {
            'instanceId':0
        });
        return url;
    }

    var onLazyReadUrl = function(displayMode, node){
        var url = '';
        (displayMode == 'classic') ? url = Routing.generate('claro_resource_children', {
            'instanceId': node.data.key
            })
        : url = Routing.generate('claro_resources_list', {
            'resourceTypeId':node.data.id
        });
        return url;
    }

    var initLinker = function(){
        ClaroUtils.sendRequest(Routing.generate('claro_resource_types'), function(data){
            //JSON.parse not working: why ?
            var resourceTypes = eval(data);
            var root = $('#ws_tree').dynatree('getTree').getRoot();
            for(var i in resourceTypes){
                var node = {
                    "id": resourceTypes[i].id,
                    "key": resourceTypes[i].type,
                    "title": resourceTypes[i].type,
                    "tooltip":  resourceTypes[i].type,
                    "shareType": 1,
                    "type": "type_"+resourceTypes[i].type,
                    "isFolder": true,
                    "isLazy": true,
                    "isTool": true
                }
                root.addChild(node);
            }
        })
    };

    function createTree(initChildren)
    {
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
                        $('#ws_form').empty().append(xhr.responseText).find('form').submit(function (e) {
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
                            $('#ws_form').empty().append(data).find('form').submit(function (e) {
                                e.preventDefault();
                                var action = $('#ws_form').find('form').attr('action');
                                action = action.replace('_instanceId', node.data.key);
                                var id = $('#ws_form').find('form').attr('id');
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

        $('#ws_tree').dynatree({
            title: 'myTree',
            initAjax: {
                url : initAjaxUrl(displayMode)
                },
            onPostInit: function(isReloading, isError){
                if(displayMode == 'linker'){
                    initLinker('#ws_tree');
                }
            },
            clickFolderMode: 1,
            selectMode: 3,
            onLazyRead: function (node) {
                node.appendAjax({
                    url: onLazyReadUrl(displayMode, node),
                    success: function (node) {
                        var children = node.getChildren();

                        if (node.isSelected()){
                            for (var i in children) {
                                children[i].select();
                            }
                        }
                    },
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
                    if(undefined != wsjsonmenu[node.data.type]){
                        bindContextMenuTree(node);
                    }
                }
            },
            onDblClick: function (node) {
                    node.expand();
                    node.activate();
            },
            onCustomRender: function (node) {
                var html = "<a id='node_"+node.data.key+"' class='dynatree-title' style='cursor:pointer;' title='"+node.data.tooltip+"'href='#'> "+node.data.title+"</a>";
                html += "<span class='ws_dynatree-custom-claro-menu' id='ws_dynatree-custom-claro-menu-"+node.data.key+"' style='cursor:pointer; color:blue;'> menu </span>";
                return html;
            },
            dnd: {
                onDragStart: function (node) {
                    var bool = true;
                    (displayMode == 'classic') ? bool = true: bool = false;
                    return bool;
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
    }
});



