(function () {
    var manager = this.ClaroResourceManager = {};
    var jsonmenu = {};

    manager.init = function(div, prefix, backButton, divForm) {

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
                    div.append(data);
                    $('.resource_menu').each(function(index, element){
                        bindContextMenu(element, divForm);
                    });

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
                    div.empty();
                    div.append(data);
                    $('.resource_menu').each(function(index, element){
                        bindContextMenu(element, divForm);
                    });
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
                    div.empty();
                    div.append(data);
                    $('.resource_menu').each(function(index, element){
                        bindContextMenu(element, divForm);
                    });
                }
                );
        })
    }

    function bindContextMenu(menuElement, divForm) {
        var type = menuElement.dataset.type;
        var menuDefaultOptions = {
            selector: '#'+menuElement.id,
            trigger: 'left',
            //See the contextual menu documentation.
            callback: function(key, options) {
                //Finds and executes the action for the right menu item.
                findMenuObject(jsonmenu[type], menuElement, key, divForm);
            }
        };

        menuDefaultOptions.items = jsonmenu[type].items;
        $.contextMenu(menuDefaultOptions);
        //Left click menu.
    };

    //Finds wich menu was fired for a node.
    //@params items is the menu object used.
    function findMenuObject(items, menuElement, menuItem, divForm)
    {
        for (var property in items.items) {
            if (property === menuItem) {
                executeMenuActions(items.items[property], menuElement, divForm);
            } else {
                if (items.items[property].hasOwnProperty('items')) {
                    findMenuObject(items.items[property], menuElement, menuItem, divForm);
                }
            }
        }
    };

    function executeMenuActions (obj, menuElement, divForm)
    {
        //Removes the placeholders in the route
        var route = obj.route;
        var compiledRoute = route.replace('_instanceId', menuElement.dataset.key);
        compiledRoute = compiledRoute.replace('_resourceId', menuElement.dataset.resourceId);
        obj.async ? executeAsync(obj, menuElement, compiledRoute, divForm) : window.location = compiledRoute;
    }

    function executeAsync(obj, menuElement, route, divForm) {

        //Delete was a special case as every node can be removed.
        (obj.name === 'delete') ? removeNode(menuElement, route) : executeRequest(menuElement, route, divForm);
    };

    function removeNode(menuElement, route) {
        alert('element not yet removed');
//        ClaroUtils.sendRequest(route, function(data, textStatus, jqXHR) {
//            if (204 === jqXHR.status) {
//                node.remove();
//            }
//        });
    };

    function executeRequest(menuElement, route, divForm) {

        ClaroUtils.sendRequest(route, function(data) {
            //If there is a form, the submission handler above is used.
            //There is no handler otherwise.
            divForm.empty().append(data).find('form').submit(function(e) {
                e.preventDefault();
                var action = divForm.find('form').attr('action');
                action = action.replace('_instanceId', menuElement.dataset.key)
                var id = divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, menuElement, divForm);
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