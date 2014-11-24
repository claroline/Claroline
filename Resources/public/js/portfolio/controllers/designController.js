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
                var existedWidget = widgets[widgets.length - 1];

                widget.column = column;
                widget.row    = existedWidget ? existedWidget.row + 1 : 1;

                widgetsManager.save(widget);
            };

            $scope.increaseRow = function(widget) {
                var newRow      = widget.row + 1;
                var nextWidgets = $filter('filter')($scope.widgets, {type: '!title', column: widget.column, row: newRow});

                if (0 >= nextWidgets.length) {
                    throw "Row/column management error.";
                }

                nextWidgets[0].row--;
                widget.row++;

                widgetsManager.save(widget);
            };

            $scope.decreaseRow = function(widget) {
                var newRow          = widget.row - 1;
                var previousWidgets = $filter('filter')($scope.widgets, {type: '!title', column: widget.column, row: newRow});

                if (0 >= previousWidgets.length) {
                    throw "Row/column management error.";
                }

                previousWidgets[0].row++;
                widget.row--;

                widgetsManager.save(widget);
            };

            $scope.$watch('portfolio.disposition', function(newValue, oldValue) {
                if (newValue !== undefined) {

                    switch(newValue) {
                        case 1:
                            $scope.cols = [1, 2];
                            break;
                        case 2:
                            $scope.cols = [1, 2, 3];
                            break;
                        case 3:
                            $scope.cols = [1, 2, 3, 4];
                            break;
                        default:
                            $scope.cols = [1];
                            break;
                    }

                    if (oldValue != newValue) {
                        switch(newValue) {
                            case 1:
                                var widgetsToUpdate = $filter('filter')($scope.widgets, {type: '!title', column: 3});
                                angular.forEach(widgetsToUpdate, function(widget, key) {
                                    widget.column = 2;
                                });
                                break;
                            case 2:
                                var widgetsToUpdate = $filter('filter')($scope.widgets, {type: '!title', column: 4});
                                angular.forEach(widgetsToUpdate, function(widget, key) {
                                    widget.column = 3;
                                });
                                break;
                            case 3:
                                break;
                            default:
                                var widgetsToUpdate = $filter('filter')($scope.widgets, {type: '!title', column: '!3'});
                                angular.forEach(widgetsToUpdate, function(widget, key) {
                                    widget.column = 1;
                                    widget.row    = (key + 1);
                                });
                                break;
                        }
                    }
                }
            });
        }]);