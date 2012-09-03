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
        var webRoot = Routing.generate('claro_admin_index')+"/../../..";

        return {
            getTemplates: function() {
                return templates;
            },
            getRoots: function(callBack){
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_roots'),
                    function(data){
                        var resources = data;
                        var parents = {};
                        var html = Twig.render(templates.thumbnailTemplate, {
                            'parents':parents,
                            'instances':resources,
                            'webRoot': webRoot
                        });
                        callBack(html);
                    });
            },
            getChildren: function(id, callBack){
                var resources = {};
                var parents = {};
                var iRequest = 0;
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_children', {
                        'instanceId': id
                    }),
                    function(res){
                        resources = res;
                        iRequest++;
                        if (iRequest == 2) {
                            var html = Twig.render(templates.thumbnailTemplate, {
                                'parents':parents,
                                'instances':resources,
                                'webRoot': webRoot
                            });
                            callBack(html);
                        }
                    });
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_parents', {
                        'instanceId': id
                    }),
                    function(par){
                        parents = par;
                        if (parents == '[]') {
                            parents = null;
                        }
                        iRequest++;
                        if (iRequest == 2) {
                            var html = Twig.render(templates.thumbnailTemplate, {
                                'parents':parents,
                                'instances':resources,
                                'webRoot': webRoot
                            });
                            callBack(html);
                        }
                    });
            },
            getFlatPaginatedThumbnails: function(page, callBack){
                var route = Routing.generate('claro_resource_paginate_all', {
                    'page':page
                });
                ClaroUtils.sendRequest(route, function(data){
                    var html = Twig.render(templates.thumbnailTemplate, {
                        'instances':data,
                        'webRoot': webRoot
                    })
                    callBack(html);
                })
            }
        }
    };
})();



