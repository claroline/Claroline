'use strict';

widgetsApp
    .factory("widgetFactory", ["$resource", "urlInterpolator", function($resource, urlInterpolator) {
        return {
            baseUrl: Routing.generate("icap_portfolio_internal_widget"),
            widgetResources: [],
            getWidget: function(type) {
                if (this.widgetResources[type]) {
                    return this.widgetResources[type];
                }

                var baseUrl = this.baseUrl + "/:type/:id";
                var widget = $resource(
                    baseUrl,
                    {
                        type: type,
                        id: "@id"
                    },
                    {
                        get:    { method: "GET" },
                        create: { method: "GET" },
                        save:   { method: "POST" },
                        update: { method: "PUT" },
                        remove: { method: "DELETE"}
                    }
                );
                widget.prototype.isNew = true;
                widget.prototype.type = type;
                widget.prototype.isEditing = false;
                widget.prototype.isUpdating = false;
                widget.prototype.isDeleting = false;
                widget.prototype.isCollapsed = false;

                widget.prototype.generateUrl = function(action) {
                    var parameters = {};
                    parameters.type = type;
                    parameters.action = action;

                    return urlInterpolator.interpolate("/widget/{{type}}/{{action}}", parameters);
                };
                widget.prototype.getRenderUrl = function() {
                    return this.generateUrl("render");
                };
                widget.prototype.getFormUrl = function() {
                    return this.generateUrl("form");
                };
                widget.prototype.setFormView = function(formView) {
                    this.views.form = formView;
                };
                widget.prototype.getType = function() {
                    return this.type;
                };
                widget.prototype.deleteChildren = function() {
                    if (this.children) {
                        var childrenToDelete = [];
                        for (var i = 0; i < this.children.length; i++) {
                            var currentChild = this.children[i];
                            if (currentChild.toDelete || (undefined !== currentChild.added && !currentChild.added)) {
                                childrenToDelete.push(currentChild);
                            }
                        }
                        for (var i = 0;i < childrenToDelete.length; i++) {
                            this.children.remove(childrenToDelete[i]);
                        }
                    }
                };

                this.widgetResources[type] = widget;

                return this.widgetResources[type];
            }
        }
    }]);