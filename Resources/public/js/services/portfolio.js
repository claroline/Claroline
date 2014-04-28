'use strict';

portfolioApp
    .factory("Portfolio", ["$resource", "widgetFactory", "widgetsConfig", "urlInterpolator", function($resource, widgetFactory, widgetsConfig, urlInterpolator){
        var url = Routing.generate("icap_portfolio_internal_portfolio") + "/:portfolioId";
        var portfolio = $resource(url, { portfolioId: "@portfolioId" }, { get: { method: "GET" } });

        portfolio.prototype.init = function() {
            angular.forEach(widgetsConfig.config, function(element, index) {
                if (!this[index]) {
                    return this[index] = [];
                }
                var widget = widgetFactory.getResource(this.id, index);
                this[index] = element.unique ? new widget(this[index]) : this[index].map(function(element) {
                    return new widget(element);
                }, this);
            }, this);
            return this;
        };

        return portfolio;
    }]);