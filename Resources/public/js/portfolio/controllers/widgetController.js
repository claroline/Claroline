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
    }]);