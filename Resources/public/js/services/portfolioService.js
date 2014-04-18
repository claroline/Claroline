'use strict';

portfolioApp
    .service('PortfolioService', ['$resource', function($resource){
        return $resource(Routing.generate('icap_portfolio_internal_portfolio') + '/:portfolioId', {}, {
            get:     { method : 'GET'},
            save:    { method : 'PUT'}
        });
    }]);