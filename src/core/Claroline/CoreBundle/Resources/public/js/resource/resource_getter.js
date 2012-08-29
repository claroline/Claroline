(function () {
    var getter = this.ClaroResourceGetter = {};

    getter.getter = function(
        thumbnailTemplate,
        listTemplate
    )
    {
        var templates = {};
        templates.thumbnailTemplate = thumbnailTemplate;
        templates.listTemplate = listTemplate;
        var getterPrefix = 'default';

        return {
            setPrefix: function(prefix){
                getterPrefix = prefix;
            },
            getPrefix: function() {
                return getterPrefix
            },
            getTemplates: function() {
                return templates;
            },
            getRoots: function(callBack){
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_renders_thumbnail', {
                        'prefix': getterPrefix
                    }),
                    function(data){
                        callBack(data);
                    }
                 )
            },
            getChildren: function(key, callBack){
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_renders_thumbnail', {
                        'parentId': key,
                        'prefix':getterPrefix
                    }),
                    function(data){
                        callBack(data);
                    }
                )
            },
            getFlatPaginatedThumbnails: function(page, callBack){
                var route = Routing.generate('claro_resource_flat_view_page', {
                    'page':page,
                    'prefix':getterPrefix
                });
                ClaroUtils.sendRequest(route, function(data){
                    callBack(data);
                })
            }
        }
    };

})();



