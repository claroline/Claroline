'use strict';

portfolioApp
    .factory("widgetsManager", ["$http", "widgetsConfig", "widgetFactory", "$q", "urlInterpolator",
        function($http, widgetsConfig, widgetFactory, $q, urlInterpolator){
        return {
            portfolioWidgets: [],
            init: function(portfolioWidgets) {
                angular.forEach(portfolioWidgets, function(rawWidget) {
                    console.log(rawWidget);
                    var widget = widgetFactory.getWidget(rawWidget.portfolio_id, rawWidget.widget_type, rawWidget.widget_id);
                    var newPortfolioWidget= new widget(rawWidget);
                    newPortfolioWidget.isNew = false;
                    this.portfolioWidgets.push(newPortfolioWidget);
                }, this);
            },
            addWidget: function(portfolioWidget) {
                var widget = widgetFactory.getWidget(portfolioWidget.portfolio_id, portfolioWidget.widget_type, portfolioWidget.widget_id);
                var newPortfolioWidget= new widget(portfolioWidget);
                newPortfolioWidget.isNew = true;
                this.portfolioWidgets.push(newPortfolioWidget);
                this.save(newPortfolioWidget);
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
            },
            save: function(widget) {
                if (null == widget.row) {
                    widget.row = 0;
                }
                if (null == widget.col) {
                    widget.col = 0;
                }

                widget.isUpdating = true;
                delete widget.copy;

                var $this = this;
                var success = function() {
                    widget.isNew = false;
                    $this.cancelEditing(widget);
                    widget.isUpdating = false;
                };
                var failed = function(error) {
                    console.error('Error occured while saving widget');
                    console.log(error);
                }

                if (widget.isNew) {
                    delete widget.id;
                }
                return widget.isNew ? widget.$save(success, failed) : widget.$update(success, failed);
            },
            cancelEditing: function(widget, rollback) {
                if (rollback) {
                    angular.copy(widget.copy, widget);
                }
                widget.isEditing = false;

                if (widget.isNew) {
                    this.portfolioWidgets.remove(widget);
                }
            }
        };
    }]);