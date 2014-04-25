'use strict';

portfolioApp
    .service('portfolioService', ['$resource', function($resource){
        return $resource(Routing.generate('icap_portfolio_internal_portfolio') + '/:portfolioId', {}, {
            get:     { method : 'GET'},
            save:    { method : 'PUT'}
        });
    }]);