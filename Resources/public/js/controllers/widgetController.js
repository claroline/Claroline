'use strict';

portfolioApp
    .controller("widgetsController", ["$scope", "$attrs", "widgetsManager", function($scope, $attrs, widgetsManager) {
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