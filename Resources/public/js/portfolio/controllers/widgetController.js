'use strict';

portfolioApp
    .controller("widgetController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.edit = function() {
            widgetsManager.edit($scope.widget);
        };

        $scope.cancelEdition = function() {
            widgetsManager.cancelEditing($scope.widget, true);
        };

        $scope.save = function() {
            return widgetsManager.save($scope.widget);
        };

        $scope.isDeletable = function() {
            return widgetsManager.isDeletable($scope.widget);
        };

        $scope.delete = function() {
            widgetsManager.delete($scope.widget);
        };

        $scope.$watchGroup(['widget.row','widget.col','widget.sizeX','widget.sizeY'], function(newValue, oldValue) {
            if (null == newValue[0]) {
                $scope.widget.row = oldValue[0];
            }

            if (null == newValue[1]) {
                $scope.widget.col = oldValue[1];
            }

            if (newValue !== oldValue) {
                $scope.widget.toSave = true;
            }
        });
    }]);