'use strict';

portfolioApp
    .controller("editWidgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.editedWidgets = widgetsManager.editing;

        $scope.cancel = function(widget) {
            angular.copy(widget.copy, widget)
            widgetsManager.cancelEditing(widget);
        };

        $scope.save = function(widget) {
            delete widget.copy;
            return widgetsManager.save(widget);
        };
    }]);