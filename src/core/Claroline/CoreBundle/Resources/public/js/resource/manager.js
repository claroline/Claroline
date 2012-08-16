(function () {
    var manager = this.ClaroResourceManager = {};
    var jsonmenu = {};

    manager.init = function(div, prefix, backButton) {

        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_menus'),
            function(data) {
                jsonmenu = JSON.parse(data);
            },
            function() {
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {
                    'prefix': prefix
                }),
                function(data){
                    div.append(data);
                    $('.resource_menu').each(function(index, element){
                        bindContextMenu(element);
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
                        bindContextMenu(element);
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
                        bindContextMenu(element);
                    });
                }
                );
        })
    }

    function bindContextMenu(menuElement) {
        var type = menuElement.dataset.type;
        var menuDefaultOptions = {
            selector: '#'+menuElement.id,
            trigger: 'left',
            //See the contextual menu documentation.
            callback: function(key, options) {
                //Finds and executes the action for the right menu item.
//                findMenuObject(jsonmenu[type], menuElement, key, isDynatree);
            }
        };

        menuDefaultOptions.items = jsonmenu[type].items;
        $.contextMenu(menuDefaultOptions);
        //Left click menu.
    };
})();