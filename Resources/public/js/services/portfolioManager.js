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
            }
        };
    }]);