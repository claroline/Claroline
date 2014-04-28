'use strict';

portfolioApp
    .factory("widgetFactory", ["$resource", "urlInterpolator", function($resource, urlInterpolator) {
        return {
            baseUrl: Routing.generate("icap_portfolio_internal_portfolio"),
            portfolioId: null,
            widgetResources: [],
            getResource: function(portfolioId, type) {
                if (this.widgetResources[type]) {
                    return this.widgetResources[type];
                }

                this.portfolioId = portfolioId;

                var baseUrl = this.baseUrl + "/:portfolioId/:type/:subtype/:id/:action";
                var widget = $resource(baseUrl,
                    {
                        type: type,
                        portfolioId: portfolioId,
                        id: "@id",
                        subtype: "@$type"
                    },
                    {
                        get:    { method: "GET" },
                        create: { method: "GET" },
                        save:   { method: "POST" },
                        update: { method: "PUT" },
                        remove: { method: "DELETE"}
                    }
                );

                widget.prototype.generateUrl = function(parameters) {
                    parameters.portfolioId = portfolioId;
                    parameters.type        = type;
                    parameters.subtype     = this.$type;

                    return urlInterpolator.interpolate(baseUrl + "/{{portfolioId}}/{{type}}/{{subtype}}/{{id}}/{{action}}", parameters);
                };
                widget.prototype.getMediaDownloadUrl = function() {
                    return this.generateUrl({action: "media"});
                };
                widget.prototype.getRenderUrl = function() {
                    return this.generateUrl({action: "render"});
                };
                widget.prototype.getUploadUrl = function() {
                    return this.generateUrl({action: "upload"});
                };
                widget.prototype.getFullUrl = function() {
                    return this.generateUrl({id: this.id, view: "detail"});
                };
                widget.prototype.getEmbedUrl = function() {
                    return this.generateUrl({action: "embed"});
                };
                widget.prototype.getSort = function() {
                    return this.generateUrl({action: "sort"});
                };
                widget.prototype.getLoadFormUrl = function() {
                    return this.generateUrl({action: "form"});
                };
                widget.prototype.setForm = function(form) {
                    this.views.form = form;
                };
                widget.prototype.setEditMode = function(e) {
                    this.focus = e, e ? this.$minified = !1 : delete this.$minified;
                };
                widget.prototype.isEditing = function() {
                    return this.focus && !this.$minified;
                };
                widget.prototype.getType = function() {
                    return type;
                };

                this.widgetResources[type] = widget;

                return this.widgetResources[type];
            }
        }
    }]);