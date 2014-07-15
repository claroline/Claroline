'use strict';

portfolioApp
    .controller("widgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.edit = function(widget) {
            widget.copy = angular.copy(widget);
            widgetsManager.edit(widget);
        };

        $scope.isDeletable = function(widget) {
            return widgetsManager.isDeletable(widget);
        };

        $scope.delete = function(widget) {
            widgetsManager.delete(widget);
        }
    }]);