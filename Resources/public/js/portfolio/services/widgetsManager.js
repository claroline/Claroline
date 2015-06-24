'use strict';

portfolioApp
    .factory("widgetsManager", ["$http", "widgetsConfig", "widgetFactory", "$q", "urlInterpolator",
        function($http, widgetsConfig, widgetFactory, $q, urlInterpolator){
        return {
            portfolioWidgets: [],
            init: function(portfolioId, portfolioWidgets) {
                angular.forEach(portfolioWidgets, function(rawWidget) {
                    var widget = widgetFactory.getWidget(portfolioId, rawWidget.type);
                    var newPortfolioWidget= new widget(rawWidget);
                    newPortfolioWidget.isNew = false;
                    this.portfolioWidgets.push(newPortfolioWidget);
                }, this);
            },
            addWidget: function(portfolioWidget) {
                var widget = widgetFactory.getWidget(portfolioWidget.portfolio_id, portfolioWidget.widget_type);
                var newPortfolioWidget= new widget(portfolioWidget);
                newPortfolioWidget.isNew = false;
                this.portfolioWidgets.push(newPortfolioWidget);
            },
            delete: function(portfolioWidget) {
                portfolioWidget.isDeleting = true;
                var self = this;
                var success = function() {
                    self.portfolioWidgets.remove(portfolioWidget);
                };
                var failed = function(error) {
                    console.error('Error occured while deleting portfolio widget');
                    console.log(error);
                }
                portfolioWidget.$delete(success, failed).then(function() {
                    portfolioWidget.isDeleting = false;
                });
            },
            getAvailableWidgetsByTpe: function(portfolioId, type) {
                var url = urlInterpolator.interpolate('/{{portfolioId}}/{{type}}', {portfolioId: portfolioId, type: type});

                var deferred = $q.defer();
                $http.get(url)
                    .success(function(data) {
                        deferred.resolve(data);
                    }).error(function(msg, code) {
                        deferred.reject(msg);
                    });
                return deferred.promise;
            }
        };
    }]);