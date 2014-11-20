'use strict';

commentsApp
    .factory("portfolioFactory", ["$resource", function($resource){
        var url = Routing.generate("icap_portfolio_internal_portfolio") + "/comments/:portfolioId";
        var portfolio = $resource(url,
            {
                portfolioId: "@id"
            },
            {
                update: { method: "PUT" }
            });

        return portfolio;
    }]);