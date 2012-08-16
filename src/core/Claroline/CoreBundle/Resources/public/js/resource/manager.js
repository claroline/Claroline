(function () {
    var manager = this.ClaroResourceManager = {};
    var jsonmenu = {};

    manager.init = function(div, prefix, backButton, divForm, selectType, submitButton) {
        selectType.hide();
        submitButton.hide();

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
                    appendThumbnails(divForm, data);
                }
            )
        });

        $('.link_navigate_instance').live('click', function(e){
            var key = e.target.dataset.key;
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {
                    'parentId': key,
                    'prefix':prefix
                }),
                function(data){
                    appendThumbnails(divForm, data);
                    selectType.show();
                    submitButton.show();
                }
                );
        })

        backButton.on('click', function(e){
            var key = e.target.dataset.key;
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {
                    'parentId': key,
                    'prefix':prefix
                }),
                function(data){
                    appendThumbnails(divForm, data);
                    if ($('#'+prefix+'_current_folder').size() == 0) {
                        selectType.hide();
                        submitButton.hide();
                    }

                });
        })

        submitButton.on('click', function(e){
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_creation_form', {
                    'resourceType': selectType.val()
                }),
                function(data) {
                    divForm.append(data);
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
                            submissionHandler(xhr, parameters, divForm);
                        });
                    })
                }
                )
            });
    }

    function appendThumbnails(div, data) {
        div.empty();
        div.append(data);
        $('.resource_menu').each(function(index, element){
            var parameters = {};
            parameters.key = element.dataset.key;
            parameters.resourceId = element.dataset.resourceId;
            parameters.type = element.dataset.type;
            bindContextMenu(parameters, div);
        });
    }

    function bindContextMenu(parameters, divForm) {
        var type = parameters.type;
        var menuDefaultOptions = {
            selector: '#'+parameters.id,
            trigger: 'left',
            //See the contextual menu documentation.
            callback: function(key, options) {
                //Finds and executes the action for the right menu item.
                findMenuObject(jsonmenu[type], parameters, key, divForm);
            }
        };

        menuDefaultOptions.items = jsonmenu[type].items;
        $.contextMenu(menuDefaultOptions);
        //Left click menu.
    };

    //Finds wich menu was fired for a node.
    //@params items is the menu object used.
    function findMenuObject(items, parameters, menuItem, divForm)
    {
        for (var property in items.items) {
            if (property === menuItem) {
                executeMenuActions(items.items[property], parameters, divForm);
            } else {
                if (items.items[property].hasOwnProperty('items')) {
                    findMenuObject(items.items[property], parameters, menuItem, divForm);
                }
            }
        }
    };

    function executeMenuActions (obj, parameters, divForm)
    {
        //Removes the placeholders in the route
        var route = obj.route;
        var compiledRoute = route.replace('_instanceId', parameters.key);
        compiledRoute = compiledRoute.replace('_resourceId', parameters.resourceId);
        obj.async ? executeAsync(obj, parameters, compiledRoute, divForm) : window.location = compiledRoute;
    }

    function executeAsync(obj, parameters, route, divForm) {

        //Delete was a special case as every node can be removed.
        (obj.name === 'delete') ? removeNode(menuElement, route) : executeRequest(parameters, route, divForm);
    };

    function removeNode(parameters, route) {
        alert('element not yet removed');
//        ClaroUtils.sendRequest(route, function(data, textStatus, jqXHR) {
//            if (204 === jqXHR.status) {
//                node.remove();
//            }
//        });
    };

    function executeRequest(parameters, route, divForm) {

        ClaroUtils.sendRequest(route, function(data) {
            //If there is a form, the submission handler above is used.
            //There is no handler otherwise.
            divForm.empty().append(data).find('form').submit(function(e) {
                e.preventDefault();
                var action = divForm.find('form').attr('action');
                action = action.replace('_instanceId', parameters.key)
                var id = divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters, divForm);
                });
            });
        });
    };

    function submissionHandler(xhr, menuElement, divForm) {
        //If there is a json response, a node was returned.
        if (xhr.getResponseHeader('Content-Type') === 'application/json') {
            var JSONObject = JSON.parse(xhr.responseText);
            var instance = JSONObject[0];
            alert('action from form done');
        //If it's not a json response, we append the response at the top of the tree.
        } else {
            divForm.empty().append(xhr.responseText).find('form').submit(function(e) {
                e.preventDefault();
                var action = divForm.find('form').attr('action');
                //If it's a form, placeholders must be removed (the twig form doesn't know the instance parent,
                //that's why placeholders are used).'
                action = action.replace('_instanceId', menuElement.dataset.key);
                action = action.replace('_resourceId', menuElement.dataset.resourceId);
                var id = divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, menuElement, divForm);
                });
            });
        }
    };


})();