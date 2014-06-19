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

        $scope.$watch('portfolio.disposition', function(newValue, oldValue) {
            switch(newValue) {
                case 1:
                    $scope.cols = [1, 2];
                    break;
                case 2:
                    $scope.cols = [1, 2, 3];
                    break;
                default:
                    $scope.cols = [];
                    break;
            }
        });
    }]);