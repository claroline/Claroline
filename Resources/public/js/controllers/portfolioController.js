'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "$filter", "portfolioManager", "widgetsManager", "$attrs", "widgetsConfig", function($scope, $filter, portfolioManager, widgetsManager, $attrs, widgetsConfig) {
        $scope.portfolio = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.portfolio.$promise.then(function () {
            $scope.widgets = widgetsManager.widgets;
        });
        $scope.widgetTypes = widgetsConfig.getTypes(true);

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }

        $scope.$watch('portfolio.disposition', function(newValue, oldValue) {
            if (newValue) {
                switch(newValue) {
                    case 1:
                        $scope.cols = [1, 2];
                        var widgetsToUpdate = $filter('filter')($scope.widgets, {type: '!title', column: 3});
                        angular.forEach(widgetsToUpdate, function(widget, key) {
                            widget.column = 2;
                        });
                        break;
                    case 2:
                        $scope.cols = [1, 2, 3];
                        break;
                    default:
                        $scope.cols = [];
                        var widgetsToUpdate = $filter('filter')($scope.widgets, {type: '!title', column: '!3'});
                        angular.forEach(widgetsToUpdate, function(widget, key) {
                            widget.column = 1;
                        });
                        break;
                }
            }
        });
    }]);