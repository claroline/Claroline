'use strict';

portfolioApp
    .controller("editWidgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.editedWidgets = widgetsManager.editing;

        $scope.cancel = function(widget) {
            widgetsManager.cancelEditing(widget, true);
        };

        $scope.save = function(widget) {
            return widgetsManager.save(widget);
        };
    }]);