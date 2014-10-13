'use strict';

portfolioApp
    .controller("widgetController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.edit = function(widget) {
            widgetsManager.edit(widget);
        };

        $scope.isDeletable = function(widget) {
            return widgetsManager.isDeletable(widget);
        };

        $scope.delete = function(widget) {
            widgetsManager.delete(widget);
        }
    }]);