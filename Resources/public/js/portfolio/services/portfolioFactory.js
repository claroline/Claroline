'use strict';

portfolioApp
    .factory("portfolioFactory", ["$resource", function($resource){
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