'use strict';

portfolioApp
    .controller("widgetController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.edit = function() {
            widgetsManager.edit($scope.portfolioWidget);
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

        $scope.$watchGroup(['portfolioWidget.row','portfolioWidget.column','portfolioWidget.sizeX','portfolioWidget.sizeY'], function(newValue, oldValue) {
            if (null == newValue[0]) {
                $scope.portfolioWidget.row = oldValue[0];
            }

            if (null == newValue[1]) {
                $scope.portfolioWidget.column = oldValue[1];
            }

            if (newValue !== oldValue) {
                $scope.portfolioWidget.toSave = true;
            }
        });
    }]);