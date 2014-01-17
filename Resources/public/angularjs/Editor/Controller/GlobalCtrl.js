'use strict';

/**
 * Global Controller
 */
function GlobalCtrl($scope, $http, HistoryFactory, PathFactory) {
    /**
     * Update Root step when path name changes
     */
    $scope.renameRootStep = function() {
        if (undefined != $scope.path.steps[0]) {
            $scope.path.steps[0].name = $scope.path.name;
        }
    };
    
    /**
     * Update History when general data change
     */
    $scope.updateHistory = function() {
        HistoryFactory.update($scope.path);
    };
}