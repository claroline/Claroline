'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "portfolioManager", "widgetsManager", "$attrs", "widgetsConfig", function($scope, portfolioManager, widgetsManager, $attrs, widgetsConfig) {
        $scope.portfolio = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.portfolio.$promise.then(function () {
            $scope.widgets = widgetsManager.widgets;
        });
        $scope.widgetTypes = widgetsConfig.getTypes(true);

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }

        $scope.getWidgets = function () {
            var widgets = [];
            for (var index = 0;index < this.widgets.length; index++) {
                if ('title' !== this.widgets[index].getType()) {
                    widgets.push(this.widgets[index]);
                }
            }

            return widgets;
        };
    }]);