(function () {
    var manager = this.ClaroResourceManager = {};

    /* private attributes */
    var buildPrefix = 'default';

    manager.rendersThumbnailRoots = function(div) {

        //get the 1st level
        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_renders_thumbnail'),
            function(data){
                div.append(data);
            }
        );
    }
})();