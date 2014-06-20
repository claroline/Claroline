'use strict';

portfolioApp
    .controller("designController", ["$scope", "$filter", "portfolioManager", "widgetsManager", function($scope, $filter, portfolioManager, widgetsManager) {
        $scope.changeDisposition = function (disposition) {
            $scope.portfolio.disposition = disposition;
            portfolioManager.save($scope.portfolio);
        }

        $scope.changeColumn = function($event, widget, column) {
            $event.preventDefault();
            $event.stopPropagation();

            widget.column = column;
            widgetsManager.save(widget);
        };

        $scope.$watch('portfolio.disposition', function(newValue, oldValue) {
            if (newValue) {
                switch(newValue) {
                    case 1:
                        $scope.cols = [1, 2];
                        var widgetsToUpdate = $filter('filter')($scope.widgets, {type: '!title', column: 3});
                        angular.forEach(widgetsToUpdate, function(widget, key) {
                            widget.column = 2;
                            widgetsManager.save(widget);
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
                            widgetsManager.save(widget);
                        });
                        break;
                }
            }
        });
    }]);