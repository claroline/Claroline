(function () {
    var manager = this.ClaroResourceManager = {};

    manager.init = function(div, prefix, backButton) {
        $('.link_navigate_instance').live('click', function(e){
            var key = e.target.dataset.key;
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {'parentId': key, 'prefix':prefix}),
                function(data){
                    div.empty();
                    div.append(data);
                }
             );
        })

        backButton.on('click', function(e){
            var key = e.target.dataset.key;
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_renders_thumbnail', {'parentId': key, 'prefix':prefix}),
                function(data){
                    div.empty();
                    div.append(data);
                }
             );
        })
    }

    manager.rendersThumbnailRoots = function(div, prefix) {
        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_renders_thumbnail', {'prefix': prefix}),
            function(data){
                div.append(data);
            }
        );
    }
})();