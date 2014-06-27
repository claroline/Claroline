'use strict';

portfolioApp
    .controller("editWidgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.editedWidgets = widgetsManager.editing;

        $scope.resourcePickerConfig = {
            isPickerMultiSelectAllowed: true,
            isPickerOnly: true,
            isWorkspace: true,
            pickerCallback: function (nodes) {
                console.log(nodes);
            }
        };

        $scope.cancel = function(widget) {
            widgetsManager.cancelEditing(widget);
        };

        $scope.save = function(widget) {
            return widgetsManager.save(widget);
        };
    }]);