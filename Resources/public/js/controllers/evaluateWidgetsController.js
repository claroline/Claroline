'use strict';

portfolioApp
    .controller("evaluateWidgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.evaluatedWidgets = widgetsManager.evaluating;

        $scope.cancel = function(widget) {
            widgetsManager.cancelEvaluating(widget, true);
        };

        $scope.save = function(widget) {
            return widgetsManager.save(widget);
        };
    }]);