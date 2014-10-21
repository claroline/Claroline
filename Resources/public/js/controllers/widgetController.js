'use strict';

portfolioApp
    .controller("widgetController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.edit = function(widget) {
            widgetsManager.edit(widget);
        };

        $scope.cancelEdition = function(widget) {
            widgetsManager.cancelEditing(widget, true);
        };

        $scope.save = function(widget) {
            return widgetsManager.save(widget);
        };

        $scope.isDeletable = function(widget) {
            return widgetsManager.isDeletable(widget);
        };

        $scope.delete = function(widget) {
            widgetsManager.delete(widget);
        }
    }]);