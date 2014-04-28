'use strict';

portfolioApp
    .factory('portfolioManager', ['Portfolio', "widgetsManager", function(Portfolio, widgetsManager){
        return {
            portfolio:   null,
            portfolioId: null,

            getPortfolio: function(portfolioId) {
                var $this = this;

                this.portfolioId = portfolioId;
                this.portfolio   = Portfolio.get({portfolioId: this.portfolioId}, function(portfolio) {
                    portfolio.init();
                    widgetsManager.init($this.portfolio);
                });

                return this.portfolio;
            }
        };
    }]);