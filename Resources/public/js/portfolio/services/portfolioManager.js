'use strict';

portfolioApp
    .factory('portfolioManager', ["Portfolio", "widgetsManager", "commentsManager", function(Portfolio, widgetsManager, commentsManager){
        return {
            portfolio:   null,
            portfolioId: null,

            getPortfolio: function(portfolioId) {
                this.portfolioId = portfolioId;
                this.portfolio   = Portfolio.get({portfolioId: this.portfolioId}, function(portfolio) {
                    widgetsManager.init(portfolioId, portfolio.widgets);
                    commentsManager.init(portfolioId, portfolio.comments);
                });

                return this.portfolio;
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
            updateViewCommentsDate: function () {
                this.portfolio.commentsViewAt = new Date();
                this.save(this.portfolio);
            }
        };
    }]);