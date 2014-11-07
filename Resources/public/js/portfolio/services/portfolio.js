'use strict';

portfolioApp
    .factory("Portfolio", ["$resource", "widgetFactory", "widgetsConfig", "urlInterpolator", function($resource, widgetFactory, widgetsConfig, urlInterpolator){
        var url = Routing.generate("icap_portfolio_internal_portfolio") + "/:portfolioId";
        var portfolio = $resource(url,
            {
                portfolioId: "@id"
            },
            {
                get:    { method: "GET" } ,
                update: { method: "PUT" }
            });

        return portfolio;
    }]);