'use strict';

portfolioApp
    .factory('portfolioManager', ["Portfolio", "widgetsManager", function(Portfolio, widgetsManager){
        return {
            portfolio:   null,
            portfolioId: null,

            getPortfolio: function(portfolioId) {
                this.portfolioId = portfolioId;
                this.portfolio   = Portfolio.get({portfolioId: this.portfolioId}, function(portfolio) {
                    widgetsManager.init(portfolioId, portfolio.widgets);
                });

                return this.portfolio;
            },
            save: function(portfolio) {
                var success = function() {
                    console.log('success');
                };
                var failed = function(error) {
                    console.error('Error occured while saving widget');
                    console.log(error);
                }

                return portfolio.$update(success, failed);
            }
        };
    }]);