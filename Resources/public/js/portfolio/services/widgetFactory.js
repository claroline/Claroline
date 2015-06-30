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
                widget.prototype.isUpdating = false;
                widget.prototype.isDeleting = false;
                widget.prototype.toSave = false;

                this.widgetResources[type] = widget;

                return this.widgetResources[type];
            }
        }
    }]);