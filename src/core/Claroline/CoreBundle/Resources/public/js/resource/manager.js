(function () {
    var manager = this.ClaroResourceManager = {};
    var jsonmenu = {};
    var construct = {};
    var pasteIds = {};
    //0 = cut; 1 = copy
    var cpd = null;
    var limit = 12;

    manager.init = function(
        div,
        prefix,
        backButton,
        divForm,
        selectType,
        submitButton,
        downloadButton,
        cutButton,
        copyButton,
        pasteButton,
        closeButton,
        flatChkBox
        ) {
        construct.div = div;
        construct.prefix = prefix;
        construct.backButton = backButton;
        construct.divForm = divForm;
        construct.selectType = selectType;
        construct.submitButton = submitButton;
        construct.downloadButton = downloadButton;
        construct.cutButton = cutButton;
        construct.copyButton = copyButton;
        construct.pasteButton = pasteButton;
        construct.closeButton = closeButton;
        construct.flatChkBox = flatChkBox;

        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_menus'),
            function(data) {
                jsonmenu = JSON.parse(data);
                for (var menu in jsonmenu) {
                    delete jsonmenu[menu].items['new'];
                }
            },
            function() {
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_renders_thumbnail', {
                        'prefix': prefix
                    }),
                    function(data){
                        appendThumbnails(div, data);
                    }
                )
            });

        $('.link_navigate_instance').live('click', function(e){
            var key = e.target.dataset.key;
            construct.divForm.empty();
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {
                    'parentId': key,
                    'prefix':prefix
                }),
                function(data){
                    appendThumbnails(div, data);
                });
        })

        backButton.on('click', function(e){
            var key = $('#'+construct.prefix+'_current_folder').attr('data-parent-id');
            construct.divForm.empty();
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {
                    'parentId': key,
                    'prefix':prefix
                }),
                function(data){
                    appendThumbnails(div, data);
                });
        })

        submitButton.on('click', function(e){
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_creation_form', {
                    'resourceType': selectType.val()
                }),
                function(data) {
                    divForm.empty().append(data);
                    divForm.find('form').submit(function(e) {
                        e.preventDefault();
                        var parameters = {};
                        parameters.key = $('#'+prefix+'_current_folder').attr('data-key')
                        parameters.resourceId = $('#'+prefix+'_current_folder').attr('data-resource-id')
                        parameters.type = $('#'+prefix+'_current_folder').attr('data-type')
                        var action = divForm.find('form').attr('action');
                        action = action.replace('_instanceId', parameters.key)
                        var id = divForm.find('form').attr('id');
                        ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                            submissionHandler(xhr, parameters);
                        });
                    })
                })
        });

        downloadButton.on('click', function(e){
            var ids = getSelectedItems();
            window.location = Routing.generate('claro_multi_export', ids);
        })

        cutButton.on('click', function(e){
            pasteIds = {};
            pasteIds = getSelectedItems();
            setLayout();
            cpd = 0;
        })

        copyButton.on('click', function(e){
            pasteIds = {};
            pasteIds = getSelectedItems();
            setLayout();
            cpd = 1;
        })

        pasteButton.on('click', function(e){
            var params = {};
            var route = '';
            params = pasteIds;
            if (cpd == 0) {
                params.newParentId = $('#'+construct.prefix+'_current_folder').attr('data-key');
                route = Routing.generate('claro_resource_multimove', params);
                ClaroUtils.sendRequest(route, function(){manager.reload()});
            } else {
                params.instanceDestinationId = $('#'+construct.prefix+'_current_folder').attr('data-key');
                route = Routing.generate('claro_resource_multi_add_workspace', params);
                ClaroUtils.sendRequest(route, function(){manager.reload()});
            }
        })

        closeButton.on('click', function(e){
            construct.divForm.empty();
        })

        flatChkBox.on('change', function(e){
            if(e.target.checked) {
                setLayout();
                var route = Routing.generate('claro_resource_count_instances');
                ClaroUtils.sendRequest(route,
                function(count){
                    construct.div.empty();
                    var paginator = buildPaginator(count);
                    div.append(paginator);

                    route = Routing.generate('claro_resource_flat_view_page', {
                        'page':1,
                        'prefix':construct.prefix
                    });
                    ClaroUtils.sendRequest(route, function(data){
                        div.prepend(data);
                        setMenu();

                    })

                    $('.instance_paginator_item').on('click', function(e){
                        console.debug(e);
                        activeCurrentPage(e.target);
//                        e.target.className = 'active'
                        route = Routing.generate('claro_resource_flat_view_page', {
                            'page':e.target.innerHTML,
                            'prefix':construct.prefix
                        });
                        ClaroUtils.sendRequest(route, function(data){
                            $('.instance_thumbnail_item').remove();
                            div.prepend(data);
                            setMenu();
                        })
                    });

                });
            } else {

                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_renders_thumbnail', {
                        'prefix': construct.prefix
                    }),
                    function(data){
                        appendThumbnails(construct.div, data);
                    }
                )
            }
        })

    }

    manager.reload = function() {
        var key = $('#'+construct.prefix+'_current_folder').attr('data-key');
        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_renders_thumbnail', {
                'parentId': key,
                'prefix':construct.prefix
            }),
            function(data){
                appendThumbnails(construct.div, data);
            });
    }

    function activeCurrentPage(link)
    {
        $('.instance_paginator_item').each(function(index, element){
            element.parentElement.className = '';
        })
        link.parentElement.className = 'active';
    }

    function buildPaginator(count)
    {
        var nbPage = Math.floor(count/limit);
        nbPage++;
        var paginator = '';
        paginator += '<div id="instances_paginator" class="pagination"><ul><li><a href="#">Prev</a></li>'
        for (var i = 0; i < nbPage;) {
            i++;
            if (i==1) {
                paginator += '<li class="active"><a class="instance_paginator_item" href="#">'+i+'</a></li>';
            } else {
                paginator += '<li><a class="instance_paginator_item" href="#">'+i+'</a></li>';
            }
        }
        paginator += '<li><a href="#">Next</a></li></ul></div>';

        return paginator;
    }

    function getSelectedItems()
    {
        var ids = {};
        var i = 0;
        $('.'+construct.prefix+'_chk_instance:checked').each(function(index, element){
            ids[i] = element.value;
            i++;
        })

        return ids;
    }

    function setMenu()
    {
        $('.resource_menu').each(function(index, element){
            var parameters = {};
            parameters.key = element.dataset.key;
            parameters.resourceId = element.dataset.resourceId;
            parameters.type = element.dataset.type;
            bindContextMenu(parameters, element);
        });
    }

    function appendThumbnails(div, data) {
        div.empty();
        div.append(data);
        setMenu();
        setLayout();
    }

    function bindContextMenu(parameters, menuElement) {
        var type = parameters.type;
        var menuDefaultOptions = {
            selector: '#'+menuElement.id,
            trigger: 'left',
            //See the contextual menu documentation.
            callback: function(key, options) {
                //Finds and executes the action for the right menu item.
                findMenuObject(jsonmenu[type], parameters, key);
            }
        };

        menuDefaultOptions.items = jsonmenu[type].items;
        $.contextMenu(menuDefaultOptions);
        //Left click menu.
    };

    //Finds wich menu was fired for a node.
    //@params items is the menu object used.
    function findMenuObject(items, parameters, menuItem)
    {
        for (var property in items.items) {
            if (property === menuItem) {
                executeMenuActions(items.items[property], parameters);
            } else {
                if (items.items[property].hasOwnProperty('items')) {
                    findMenuObject(items.items[property], parameters, menuItem);
                }
            }
        }
    };

    function executeMenuActions (obj, parameters)
    {
        //Removes the placeholders in the route
        var route = obj.route;
        var compiledRoute = route.replace('_instanceId', parameters.key);
        compiledRoute = compiledRoute.replace('_resourceId', parameters.resourceId);
        obj.async ? executeAsync(obj, parameters, compiledRoute) : window.location = compiledRoute;
    }

    function executeAsync(obj, parameters, route) {

        //Delete was a special case as every node can be removed.
        (obj.name === 'delete') ? removeNode(parameters, route) : executeRequest(parameters, route);
    };

    function removeNode(parameters, route) {
        ClaroUtils.sendRequest(route, function(data, textStatus, jqXHR) {
            if (204 === jqXHR.status) {
                $('#'+construct.prefix+"_instance_"+parameters.key).remove();
            }
        });
    };

    function executeRequest(parameters, route) {

        ClaroUtils.sendRequest(route, function(data) {
            //If there is a form, the submission handler above is used.
            //There is no handler otherwise.
            construct.divForm.empty().append(data).find('form').submit(function(e) {
                e.preventDefault();
                var action = construct.divForm.find('form').attr('action');
                action = action.replace('_instanceId', parameters.key)
                var id = construct.divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters);
                });
            });
        });
    };

    function submissionHandler(xhr, parameters) {
        //If there is a json response, a node was returned.
        if (xhr.getResponseHeader('Content-Type') === 'application/json') {
            manager.reload();
        //If it's not a json response, we append the response at the top of the tree.
        } else {
            construct.divForm.empty().append(xhr.responseText).find('form').submit(function(e) {
                e.preventDefault();
                var action = construct.divForm.find('form').attr('action');
                //If it's a form, placeholders must be removed (the twig form doesn't know the instance parent,
                //that's why placeholders are used).'
                action = action.replace('_instanceId', parameters.key);
                action = action.replace('_resourceId', parameters.resourceId);
                var id = construct.divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters);
                });
            });
        }
    };

    function setLayout() {
        if(construct.flatChkBox.is(':checked')){
            construct.pasteButton.attr('disabled', 'disabled');
            construct.backButton.hide();
        } else {
            if($.isEmptyObject(pasteIds) || $('#'+construct.prefix+'_current_folder').size() == 0){
                construct.pasteButton.attr('disabled', 'disabled');
            } else {
                construct.pasteButton.removeAttr('disabled');
            }
            construct.backButton.show();
            if ($('#'+construct.prefix+'_current_folder').size() == 0) {
                construct.selectType.hide();
                construct.submitButton.hide();
            } else {
                construct.selectType.show();
                construct.submitButton.show();
            }
        }
    }
})();