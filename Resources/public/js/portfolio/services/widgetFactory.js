'use strict';

portfolioApp
    .factory("widgetFactory", ["$resource", "urlInterpolator", function($resource, urlInterpolator) {
        return {
            baseUrl: Routing.generate("icap_portfolio_internal_portfolio"),
            portfolioId: null,
            widgetResources: [],
            getWidget: function(portfolioId, type) {
                if (this.widgetResources[type]) {
                    return this.widgetResources[type];
                }

                this.portfolioId = portfolioId;

                var baseUrl = this.baseUrl + "/:portfolioId/:type/:id";
                var widget = $resource(
                    baseUrl,
                    {
                        type: type,
                        portfolioId: portfolioId,
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
                widget.prototype.toSave = false;

                widget.prototype.generateUrl = function(parameters) {
                    parameters.portfolioId = portfolioId;
                    parameters.type        = type;

                    return urlInterpolator.interpolate("/{{portfolioId}}/{{type}}/{{action}}", parameters);
                };
                widget.prototype.getRenderUrl = function() {
                    return this.generateUrl({action: "render"});
                };
                widget.prototype.getFormUrl = function() {
                    return this.generateUrl({action: "form"});
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