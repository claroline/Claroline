'use strict';

portfolioApp
    .controller("widgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.edit = function(widget) {
            widgetsManager.edit(widget);
        };

        $scope.evaluate = function(widget) {
            widgetsManager.evaluate(widget);
        };

        $scope.isDeletable = function(widget) {
            return widgetsManager.isDeletable(widget);
        };

        $scope.delete = function(widget) {
            widgetsManager.delete(widget);
        }
    }]);