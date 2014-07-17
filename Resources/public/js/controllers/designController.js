'use strict';

portfolioApp
    .controller("designController", ["$scope", "$filter", "portfolioManager", "widgetsManager",
        function($scope, $filter, portfolioManager, widgetsManager) {
            $scope.changing = false;

            $scope.changeDisposition = function (disposition) {
                if (!$scope.changing && disposition !== $scope.portfolio.disposition) {
                    $scope.changing = true;
                    $scope.portfolio.disposition = disposition;
                    portfolioManager.save($scope.portfolio).then(function (data) {
                        $scope.changing = false;
                    });
                }
            }

            $scope.changeColumn = function(widget, column) {
                var widgets = $filter('orderBy')($filter('filter')($scope.widgets, {type: '!title', column: column}), '+row');

                widget.column = column;
                widget.row    = widgets[widgets.length - 1].row + 1;

                widgetsManager.save(widget);
            };

            $scope.increaseRow = function(widget) {
                var newRow     = widget.row + 1;
                var nextWidget = $filter('filter')($scope.widgets, {type: '!title', column: widget.column, row: newRow});

                nextWidget[0].row--;
                widget.row++;

                widgetsManager.save(nextWidget[0]);
                widgetsManager.save(widget);
            };

            $scope.decreaseRow = function(widget) {
                var newRow         = widget.row - 1;
                var previousWidget = $filter('filter')($scope.widgets, {type: '!title', column: widget.column, row: newRow});

                previousWidget[0].row++;
                widget.row--;

                widgetsManager.save(previousWidget[0]);
                widgetsManager.save(widget);
            };

            $scope.$watch('portfolio.disposition', function(newValue, oldValue) {
                if (newValue !== undefined) {
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