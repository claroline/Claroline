'use strict';

commentsApp
    .factory('portfolioManager', ["$http", "$q", function($http, $q){
        return {
            portfolios: [],

            getPortfolios: function() {
                    var deferred = $q.defer();
                    $http.get(Routing.generate("icap_portfolio_internal_portfolio"))
                        .success(function(data) {
                            data.$resolved = true;
                            deferred.resolve(data);
                        }).error(function(msg, code) {
                            deferred.reject(msg);
                        });
                    return deferred.promise;
            }
        };
    }]);