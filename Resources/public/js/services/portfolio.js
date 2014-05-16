'use strict';

portfolioApp
    .factory("Portfolio", ["$resource", "widgetFactory", "widgetsConfig", "urlInterpolator", function($resource, widgetFactory, widgetsConfig, urlInterpolator){
        var url = Routing.generate("icap_portfolio_internal_portfolio") + "/:portfolioId";
        var portfolio = $resource(url, { portfolioId: "@portfolioId" }, { get: { method: "GET" } });

        portfolio.prototype.init = function() {
            angular.forEach(widgetsConfig.config, function(widgetTypeConfig, widgetType) {
                if (!this[widgetType]) {
                    return;
                }
                var widget = widgetFactory.getResource(this.id, widgetType);
                this[widgetType] = widgetTypeConfig.isUnique ? [new widget(this[widgetType])] : this[widgetType].map(function(element) {
                    return new widget(element);
                }, this);
            }, this);
            return this;
        };

        return portfolio;
    }]);