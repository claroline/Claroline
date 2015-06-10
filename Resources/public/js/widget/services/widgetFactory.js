'use strict';

widgetsApp
    .factory("widgetFactory", ["$resource", "urlInterpolator", function($resource, urlInterpolator) {
        return {
            baseUrl: Routing.generate("icap_portfolio_internal_portfolio"),
            widgetResources: [],
            getWidget: function(type) {
                if (this.widgetResources[type]) {
                    return this.widgetResources[type];
                }

                var baseUrl = this.baseUrl + "/widget/:type/:id";
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
                widget.prototype.editing     = false;
                widget.prototype.new         = true;
                widget.prototype.type        = type;
                widget.prototype.updating    = false;
                widget.prototype.toSave      = false;

                widget.prototype.generateUrl = function(action) {
                    var parameters = {};
                    parameters.type = type;
                    parameters.action = action;

                    return urlInterpolator.interpolate("/{{type}}/{{action}}", parameters);
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
                widget.prototype.setEditMode = function(isEditing) {
                    this.editing = isEditing;
                };
                widget.prototype.isEditing = function() {
                    return this.editing;
                };
                widget.prototype.getType = function() {
                    return this.type;
                };
                widget.prototype.setNewMode = function(isNew) {
                    this.new = isNew;

                    return this;
                };
                widget.prototype.isNew = function() {
                    return this.new;
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
                widget.prototype.setUpdatingMode = function(isUpdating) {
                    this.updating = isUpdating;
                };
                widget.prototype.isUpdating = function() {
                    return this.updating;
                };

                this.widgetResources[type] = widget;

                return this.widgetResources[type];
            }
        }
    }]);