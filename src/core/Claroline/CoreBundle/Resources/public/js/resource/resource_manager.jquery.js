/*
 * dependencies: dynatree, contextualMenu, jquery, some templates.
 * Jquery plugin.
 * The picker mode is not fully supported yet.
 * The displayMode should be initialized from a cookie but it's not the case yet.
 */
$(function() {
    var jsonmenu = {};
    var jsonroots = '';
    $.fn.extend({
        claroResourceManager: function(options) {
            var params = $.extend({
                mode: 'manager',
                displayMode: 'classic',
                checkbox: true,
                //Function called on double click for the picker mode.
                resourcePickedHandler: function(resourceId) {
                    alert('DEFAULT SUBMIT HANDLER MUST BE CHANGED');
                }
            }, options);
            return this.each(function() {
                var currentDiv = $(this);
                var moveForm = Twig.render(move_resource_form);

                //Gets the json for contextual menus.
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_menus'),
                    function(data) {
                        jsonmenu = JSON.parse(data);
                        if ('picker' === params.mode) {
                            for (var menu in jsonmenu) {
                                delete jsonmenu[menu].items['delete'];
                                delete jsonmenu[menu].items.properties;
                            }
                            delete jsonmenu.directory;
                        }
                    },
                    function() {
                        //Gets the workspace roots for the current user.
                        ClaroUtils.sendRequest(Routing.generate('claro_resource_roots'),
                            function(data) {
                                jsonroots = data;
                                createDivTree(currentDiv);
                            });
                        });

                /**
                 * Generates the basic HTML for the resource manager.
                 *
                 * @param div the div id where the manager is built.
                 */
                function createDivTree(div) {
                    //Gets the resource types (will be needed later for the linker mode & the filters).
                    ClaroUtils.sendRequest(Routing.generate('claro_resource_types'),
                        function(rtdata) {
                            var content = Twig.render(resource_filter, {
                                resourceTypes: rtdata,
                                workspaceroots: jsonroots
                            });
                            div.append(content);
                            //Sets the correct displayMode from the cookie or from the url.
                            var parameters = ClaroUtils.getUriParameters();
                            if(parameters['mode'] != undefined) {
                                params.displayMode = parameters['mode'];
                                $('#ct_switch_mode').val(params.displayMode);
                            } else {
                                var array = ClaroUtils.splitCookieValue(document.cookie);
                                if(array[' displayMode'] != undefined) {
                                    params.displayMode = array[' displayMode'];
                                    $('#ct_switch_mode').val(params.displayMode);
                                }
                            }
                            if (true === params.checkbox) {
                                //On download event.
                                $('#ct_download').live('click', function() {
                                    var children = $('#source_tree').dynatree('getTree').getSelectedNodes();
                                    var parameters = {};
                                    //Generates a usable instance list id for the php controller.
                                    for (var i in children) {
                                        if (children[i].isTool !== true) {
                                            parameters[i] = children[i].data.instanceId;
                                            if (children[i].getParent().data.isTool === true) {
                                                parameters[i] = children[i].getParent().data.type + '_' + children[i].data.instanceId;
                                            }
                                        }
                                    }
                                    parameters.type = params.displayMode;
                                    window.location = Routing.generate('claro_multi_export', parameters);
                                });
                            }

                            //On switch mode event.
                            $('#ct_switch_mode').change(function() {
                                params.displayMode = $('#ct_switch_mode').val();
                                if (params.displayMode == 'spiral') {
                                    window.location = Routing.generate('claro_resource_accessibility_manager');
                                } else {
                                    //The tree must be reloaded.
                                    $('#source_tree').dynatree('destroy');
                                    $('#source_tree').empty();
                                    $('#folder_content').empty();
                                    createTree('#source_tree');
                                    //cookie for ... wich is the currentDisplayMode.
                                    $.cookie('displayMode', params.displayMode);
                                }
                            });

                            setFilters();
                            createTree('#source_tree');
                        });
                }

                /**
                 * Sets a filter on the datas.
                 */
                function setFilters() {
                    $('#ct_filter').click(function() {
                        showNodes();
                        showBlobs();

                        if (params.displayMode == 'classic') {
                            if ($('#select_root').val() !== null) {
                                filterTreeByWorkspace(($('#select_root').val()));
                            }

                            if ($('#select_type').val() !== null) {
                                filterTreeByType($('#select_type').val());
                            }

                            if ($('#rf_date_from').val() !== '') {
                                filterTreeFromDate($('#rf_date_from').val());
                            }

                            if ($('#rf_date_to').val() !== '') {
                                filterTreeToDate($('#rf_date_to').val());
                            }
                        }

                        if (params.displayMode == 'linker') {
                            if ($('#select_root').val()) {
                                var types = $('#source_tree').dynatree('getRoot').getChildren();
                                for (var i in types) {
                                    filterTreeByWorkspace($('#select_root').val(), types[i]);
                                }
                            }

                            if ($('#select_type').val() !== null) {
                                filterTreeByType($('#select_type').val());
                            }

                            if ($('#rf_date_from').val() !== '') {
                                filterTreeFromDate($('#rf_date_from').val());
                            }

                            if ($('#rf_date_to').val() !== '') {
                                filterTreeToDate($('#rf_date_to').val());
                            }
                        }

                        if (params.displayMode == 'hybrid') {
                            if ($('#select_root').val() !== null) {
                                filterTreeByWorkspace(($('#select_root').val()));
                            }

                            if ($('#select_type').val() !== null) {
                                filterBlobByType($('#select_type').val());
                            }

                            if ($('#rf_date_from').val() !== '') {
                                filterBlobFromDate($('#rf_date_from').val());
                            }

                            if ($('#rf_date_to').val() !== '') {
                                filterBlobToDate($('#rf_date_to').val());
                            }
                        }

                    });

                    var filterBlobByType = function(searchArray) {
                        $('.resource_figure').each(function(i) {
                              if (0 > searchArray.indexOf(this.getAttribute('data-type'))) {
                                  $('#' + this.getAttribute('id')).hide();
                              }
                        });
                    }

                    var filterBlobFromDate = function(date) {
                       $('.resource_figure').each(function(i) {
                              if (this.getAttribute('data-date_instance_creation') < date) {
                                  $('#' + this.getAttribute('id')).hide();
                              }
                        });
                    }

                    var filterBlobToDate = function(date) {
                       $('.resource_figure').each(function(i) {
                              if (this.getAttribute('data-date_instance_creation') >= date) {
                                  $('#' + this.getAttribute('id')).hide();
                              }
                        });
                    }

                    var filterTreeByType = function(searchArray, targetNode) {
                        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
                        startNode.visit(function(node) {
                            if (node.isVisible() && node.data.title) {
                                if (searchArray.indexOf(node.data.type) >= 0 || node.data.type == 'directory') {

                                } else {
                                    $(node.li).hide();
                                }
                            }
                        });
                    };

                    var filterTreeByWorkspace = function(searchArray, targetNode) {
                        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
                        startNode.visit(function(node) {
                            if (node.isVisible() && node.data.title) {
                                if (searchArray.indexOf(node.data.workspaceId) >= 0) {

                                } else {
                                    $(node.li).hide();
                                }
                            }
                        });
                    }

                    var filterTreeFromDate = function(date, targetNode) {
                        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
                        startNode.visit(function(node) {
                            if (node.isVisible() && node.data.title && node.data.dateInstanceCreation) {
                                if (node.data.dateInstanceCreation >= date || node.data.type == 'directory') {

                                } else {
                                    $(node.li).hide();
                                }
                            }
                        });
                    }

                    var filterTreeToDate = function(date, targetNode) {
                        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
                        startNode.visit(function(node) {
                            if (node.isVisible() && node.data.title) {
                                if (node.data.dateInstanceCreation <= date || node.data.type == 'directory') {

                                } else {
                                    $(node.li).hide();
                                }
                            }
                        });
                    }

                    var showNodes = function(targetNode) {
                        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
                        startNode.visit(function(node) {
                            $(node.li).show();
                        });
                    };

                    var showBlobs = function() {
                        $('.resource_figure').show();
                    }
                }

                /**
                 * Binds the contextual menu on a item.
                 *
                 * @param node a dynatree node (or a fake contaning the needed datas: see createTmpNode).
                 * @param selector the item id.
                 * @param isDynatree
                 */
                function bindContextMenu(node, selector, isDynatree) {
                    var type = node.data.type;
                    var menuDefaultOptions = {
                        selector: selector,
                        //See the contextual menu documentation.
                        callback: function(key, options) {
                            //Finds and executes the action for the right menu item.
                            findMenuObject(jsonmenu[type], node, key);
                        }
                    };

                    menuDefaultOptions.items = jsonmenu[type].items;
                    $.contextMenu(menuDefaultOptions);
                    //Left click menu.
                    var additionalMenuOptions = $.extend(menuDefaultOptions, {
                        selector: '#dynatree-custom-claro-menu-' + node.data.key,
                        trigger: 'left'
                    });

                    $.contextMenu(additionalMenuOptions);

                    //Executes the menu action.
                    var executeMenuActions = function(obj, node)
                    {
                        //Sometimes the menu action will display a form. This is the submission handler.
                        var submissionHandler = function(xhr) {
                            //If there is a json response, a node was returned.
                            if (xhr.getResponseHeader('Content-Type') === 'application/json') {
                                var JSONObject = JSON.parse(xhr.responseText);
                                var instance = JSONObject[0];
                                var newNode = {
                                    title: instance.title,
                                    key: instance.key,
                                    copy: instance.copy,
                                    instanceCount: instance.instanceCount,
                                    shareType: instance.shareType,
                                    resourceId: instance.resourceId
                                };

                                if (instance.type === 'directory') {
                                    newNode.isFolder = true;
                                }

                                if (isDynatree == true) {
                                    //If the node is unknown, we reload the active node.
                                    if (node.data.instanceId !== newNode.instanceId) {
                                        node.appendAjax({
                                            url: onLazyReadUrl(node)
                                        });
                                        node.expand();
                                    //OtherwIse the node was edited and we set the new parameters.
                                    } else {
                                        node.data.title = newNode.title;
                                        node.data.shareType = newNode.shareType;
                                        node.render();
                                    }
                                }
                                $('#ct_tree').show();
                                $('#ct_form').empty();
                            //If it's not a json response, we append the response at the top of the tree.
                            } else {
                                $('#ct_form').empty().append(xhr.responseText).find('form').submit(function(e) {
                                    e.preventDefault();
                                    var action = $('#ct_form').find('form').attr('action');
                                    //If it's a form, placeholders must be removed (the twig form doesn't know the instance parent,
                                    //that's why placeholders are used).'
                                    action = action.replace('_instanceId', node.data.instanceId);
                                    action = action.replace('_resourceId', node.data.resourceId);
                                    var id = $('#ct_form').find('form').attr('id');
                                    ClaroUtils.sendForm(action, document.getElementById(id), submissionHandler);
                                });
                            }
                        };

                        //Sometimes the action must be executed asynchronously.
                        var executeAsync = function(obj, node, route) {
                            //Delete is a special case. See below.
                            var removeNode = function() {
                                ClaroUtils.sendRequest(route, function(data, textStatus, jqXHR) {
                                    if (204 === jqXHR.status) {
                                        node.remove();
                                    }
                                });
                            };
                            //Executes the Ajax request for the menu action.
                            var executeRequest = function() {
                                ClaroUtils.sendRequest(route, function(data) {
                                    $('#ct_tree').hide();
                                    //If there is a form, the submission handler above is used.
                                    //There is no handler otherwise.
                                    $('#ct_form').empty().append(data).find('form').submit(function(e) {
                                        e.preventDefault();
                                        var action = $('#ct_form').find('form').attr('action');
                                        action = action.replace('_instanceId', node.data.instanceId);
                                        var id = $('#ct_form').find('form').attr('id');
                                        ClaroUtils.sendForm(action, document.getElementById(id), submissionHandler);
                                    });
                                });
                            };
                            //Delete was a special case as every node can be removed.
                            (obj.name === 'delete') ? removeNode(node, route) : executeRequest(node, route);
                        };

                        //Removes the placeholders in the route
                        var route = obj.route;
                        var compiledRoute = route.replace('_instanceId', node.data.instanceId);
                        compiledRoute = compiledRoute.replace('_resourceId', node.data.resourceId);
                        obj.async ? executeAsync(obj, node, compiledRoute) : window.location = compiledRoute;
                    };

                    //Finds wich menu was fired for a node.
                    //@params items is the menu object used.
                    var findMenuObject = function(items, node, menuItem)
                    {
                        for (var property in items.items) {
                            if (property === menuItem) {
                                executeMenuActions(items.items[property], node);
                            } else {
                                if (items.items[property].hasOwnProperty('items')) {
                                    findMenuObject(items.items[property], node, menuItem);
                                }
                            }
                        }
                    };
                };

                /**
                 * Creates a fake dynatree node (usefull for generating the contextual menu if the menu is not bound
                 * to a dynatree node because the bindContextMenu function is dynatree node informations. This node will
                 * contains all the needed datas).
                 */
                function createTmpNode(jsonNode)
                {
                    var tmpNode = {};
                    tmpNode.data = {};
                    tmpNode['data'].type = jsonNode.type;
                    tmpNode['data'].instanceId = jsonNode.instanceId;
                    tmpNode['data'].resourceId = jsonNode.resourceId;

                    return tmpNode;
                }

                /**
                 * Fired when the user clicks on a dynatree node name.
                 *
                 * @param node the dynatree node.
                 */
                function onClickItem(node) {
                    //on hybrid mode, "blobs" are displayed.
                    if (params.displayMode == 'hybrid') {
                        $('#folder_content').empty();

                        node.appendAjax({
                            url: onLazyReadUrl(node)
                        });

                        var route = Routing.generate('claro_resource_children', {
                            'instanceId': node.data.instanceId
                        });

                        ClaroUtils.sendRequest(route, function(children) {
                            for (var i in children) {
                                if (children[i].type !== 'directory') {
                                    var title = 'instance' + children[i].key;
                                    var imagePath = ClaroUtils.findLoadedJsPath('resource_manager.jquery.js') + '/../../../images/resources/icon/biblio_spiral.png';
                                    $('#folder_content').append(
                                        '<figure id="figure_' + children[i].instanceId + '"class="resource_figure" data-type="' + children[i].type + '" data-date_instance_creation="' + children[i].dateInstanceCreation + '"><img title="' + children[i].tooltip + '"id="' + title + '" src="' + imagePath + '"><figcaption>' + children[i].title + '</figcaption></figure>'
                                        );
                                    var tmpNode = createTmpNode(children[i]);
                                    bindContextMenu(tmpNode, '#' + title, false);
                                }
                            }
                        });
                    }
                }

                /**
                 * Gets correct url when a node is lazy loaded.
                 *
                 * @params node the dynatree node loaded.
                 */
                function onLazyReadUrl(node) {
                    var url = '';
                    if (params.displayMode === 'classic') {
                        url = Routing.generate('claro_resource_children', {
                            'instanceId': node.data.instanceId
                            });
                    }
                    if (params.displayMode === 'hybrid') {
                        url = Routing.generate('claro_resource_children', {
                            'instanceId': node.data.instanceId,
                            //always directory
                            'resourceTypeId': node.data.typeId
                            });
                    }
                    if (params.displayMode === 'linker') {
                        url = Routing.generate('claro_resources_list', {
                            'resourceTypeId': node.parent.data.id,
                            'rootId': node.data.instanceId
                            });
                    }

                    return url;
                };

                /**
                 * Creates the dynatree datatee.
                 *
                 * @params treeId the tree id
                 */
                function createTree(treeId)
                {
                    //We already loaded the workspaces roots at the beginning, so we know the 1st level of the tree.
                    var initTree = function() {
                        ClaroUtils.sendRequest(Routing.generate('claro_dashboard_resources'),function(data){
                            var children = data;
                            dynatreeCreation(children);
                        });
                    };

                    //Drag&drop drop node event.
                    var dropNode = function(node, sourceNode, hitMode)
                    {
                        $('#ct_form').empty().append(moveForm);
                        $('#move_resource_form_submit').click(function(e) {
                            e.preventDefault();
                            //form needed (the resource must be copied or moved ?)
                            var option = ClaroUtils.getCheckedValue(document.forms.move_resource_form.options);
                            var route = {};

                            if ('move' === option) {
                                route = {
                                    'name': 'claro_resource_move',
                                    'parameters': {
                                        'instanceId': sourceNode.data.instanceId,
                                        'newParentId': node.data.instanceId
                                    }
                                };

                                ClaroUtils.sendRequest(route, function(data) {
                                    //We always reload everything because the name could be changed by the core.
                                    node.reloadChildren();
                                    sourceNode.getParent().reloadChildren();
                                });

                                $('#ct_form').empty();
                            } else {
                                route = {
                                    'name': 'claro_resource_add_workspace',
                                    'parameters': {
                                        'instanceId': sourceNode.data.key,
                                        'instanceDestinationId': node.data.instanceId
                                    }
                                };
                                ClaroUtils.sendRequest(route, function(data) {
                                    //We always reload everything because the name could be changed by the core
                                    node.reloadChildren();
                                });

                                $('#ct_form').empty();
                            }
                        });
                    }
                //1st children level.
                initTree();

                var dynatreeCreation = function(children){
                    //Dynatree initialization.
                    $(treeId).dynatree({
                        checkbox: true,
                        persist: true,
                        cookieId: 'dynatree_' + params.displayMode,
                        imagePath: ClaroUtils.findLoadedJsPath('resource_manager.jquery.js') + '../../../../../../icons/',
                        title: 'myTree',
                        children: children,
                        clickFolderMode: 1,
                        selectMode: 3,
                        onLazyRead: function(node) {
                            node.appendAjax({
                                url: onLazyReadUrl(node),
                                success: function(node) {
                                    var children = node.getChildren();
                                    if (node.isSelected()) {
                                        for (var i in children) {
                                            children[i].select();
                                        }
                                    }
                                },
                                error: function(node, XMLHttpRequest, textStatus, errorThrown) {
                                    if (XMLHttpRequest.status === 403) {
                                        ClaroUtils.ajaxAuthenticationErrorHandler(function() {
                                            window.location.reload();
                                        });
                                    } else {
                                        alert('this node could not be loaded');
                                    }
                                }
                            });
                        },
                        onCreate: function(node, span) {
                            if (node.data.hasOwnProperty('type')) {
                                if (node.data.isTool !== true) {
                                    bindContextMenu(node, '#node_' + node.data.key, true);
                                }
                            }
                        },
                        onClick: function(node, event) {
                            if(event.target.className == 'dynatree-title') {
                                onClickItem(node);
                            }
                        },
                        onDblClick: function(node) {
                            if (params.mode === 'picker' && node.data.type !== 'resourceType') {
                                (node.shareType === 0) ? alert("you can't share this resource") : params.resourcePickedHandler(node.data.resourceId);
                            } else {
                                node.expand();
                                node.activate();
                            }
                        },
                        onCustomRender: function(node) {
                            var html = "<a id='node_" + node.data.key + "' class='dynatree-title' style='cursor:pointer;' title='" + node.data.tooltip + "'href='#'> " + node.data.title + '</a>';
                            html += "<span class='dynatree-custom-claro-menu' id='dynatree-custom-claro-menu-" + node.data.key + "' style='cursor:pointer; color:blue;'> menu </span>";
                            return html;
                        },
                        dnd: {
                            onDragStart: function(node) {
                                if (params.mode === 'picker' || params.displayMode === 'linker') {
                                    return false;
                                }
                            },
                            onDragStop: function(node) {
                            },
                            autoExpandMS: 1000,
                            preventVoidMoves: true,
                            onDragEnter: function(node, sourceNode) {
                                return true;
                            },
                            onDragOver: function(node, sourceNode, hitMode) {
                                if (node.isDescendantOf(sourceNode) || node.data.type != 'directory' || node == $(treeId).dynatree('getTree')) {
                                    return false;
                                }
                            },
                            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
                                if (node.isDescendantOf(sourceNode) || node.data.type != 'directory' || node == $(treeId).dynatree('getTree')) {
                                    return false;
                                }
                                else {/*
                                    if(sourceNode.data.shareType == 0){
                                        alert('this resource is private');
                                        return false;
                                    }*/

                                    dropNode(node, sourceNode, hitMode);
                                }
                            }
                        }
                    });
                }
                }
                return (this);
            });
        }

    });
});
