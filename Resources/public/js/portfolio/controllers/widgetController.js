'use strict';

portfolioApp
    .controller("widgetController", ["$scope", "widgetsManager", "$modal", "$timeout", function($scope, widgetsManager, $modal, $timeout) {
        $scope.edit = function() {
            widgetsManager.edit($scope.portfolioWidget);
            $scope.editWidget();
        };

        $scope.editWidget = function() {
            var modalInstance = $modal.open({
                backdrop: false,
                animation: true,
                templateUrl: 'widget_picker_modal.html',
                controller: 'widgetPickerController',
                size: 'lg',
                resolve: {
                    portfolioWidgets: function () {
                        return widgetsManager.getAvailableWidgetsByTpe($scope.portfolioWidget.portfolio_id, $scope.portfolioWidget.widget_type);
                    },
                    selectedPortfolioWidget: function() {
                        return $scope.portfolioWidget;
                    }
                }
            });

            modalInstance.result.then(function (selectedWidget) {
                $scope.portfolioWidget.widget_id = selectedWidget.widget_id;
                widgetsManager.save($scope.portfolioWidget);
            }, function () {
                widgetsManager.cancelEditing($scope.portfolioWidget);
            });

            /*
            Small code to avoid https://github.com/angular-ui/bootstrap/issues/3633
            Solution come from https://github.com/angular-ui/bootstrap/issues/3633#issuecomment-110166992
             */
            modalInstance.result.finally(function() {
                $timeout(function() {
                    $('.modal:last').trigger('$animate:close');
                    $timeout(function() {
                        $('.modal-backdrop:last').trigger('$animate:close');
                    }, 100);
                }, 100);
            });
        };

        $scope.cancelEdition = function() {
            widgetsManager.cancelEditing($scope.portfolioWidget, true);
        };

        $scope.save = function() {
            return widgetsManager.save($scope.portfolioWidget);
        };

        $scope.delete = function() {
            widgetsManager.delete($scope.portfolioWidget);
        };

        $scope.$watchGroup(['portfolioWidget.row','portfolioWidget.col','portfolioWidget.sizeX','portfolioWidget.sizeY'], function(newValue, oldValue) {
            if (null == newValue[0]) {
                $scope.portfolioWidget.row = oldValue[0];
            }

            if (null == newValue[1]) {
                $scope.portfolioWidget.col = oldValue[1];
            }
            if (newValue !== oldValue) {
                $scope.portfolioWidget.toSave = true;
            }
        });
    }]);