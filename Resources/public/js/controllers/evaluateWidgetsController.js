'use strict';

portfolioApp
    .controller("evaluateWidgetsController", ["$scope", "widgetsManager", function($scope, widgetsManager) {
        $scope.save = function(widget) {
            console.log(widget);
            return widgetsManager.save(widget);
        };
    }]);