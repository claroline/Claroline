'use strict';

portfolioApp
    .factory('portfolioManager', ['portfolioService', "widgetsManager", function(portfolioService, widgetsManager){
        return {
            portfolio:   null,
            portfolioId: null,

            getPortfolio: function(portfolioId) {
                this.portfolioId = portfolioId;
                this.portfolio   = portfolioService.get({portfolioId: this.portfolioId});

                this.portfolio.$promise.then(function(portfolio) {
                    widgetsManager.init(portfolio);
                });

                return this.portfolio;
            }
        };
    }]);