'use strict';

commentsApp
    .factory('portfolioManager', ["$http", "$q", "portfolioFactory", function($http, $q, portfolioFactory){
        return {
            portfolios: [],
            getPortfolios: function() {
                var deferred = $q.defer();
                $http.get(Routing.generate("icap_portfolio_internal_portfolio"))
                    .success(function(data) {
                        var portfolios = [];
                        angular.forEach(data, function(rawPortfolio) {
                            portfolios.push(new portfolioFactory(rawPortfolio));
                        });
                        portfolios.$resolved = true;
                        deferred.resolve(portfolios);
                    }).error(function(msg, code) {
                        deferred.reject(msg);
                    });
                return deferred.promise;
            },
            save: function(portfolio) {
                var success = function() {
                };
                var failed = function(error) {
                    console.error('Error occured while saving widget');
                    console.log(error);
                }

                return portfolio.$update(success, failed);
            },
            updateViewCommentsDate: function (portfolio) {
                portfolio.commentsViewAt = new Date();
                this.save(portfolio);
            }
        };
    }]);