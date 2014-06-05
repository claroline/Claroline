'use strict';

/**
 * Global Controller
 */
function GlobalCtrl($scope) {
    /**
     * Update Root step when path name changes
     */
    $scope.renameRootStep = function() {
        if (undefined != $scope.path.steps[0]) {
            $scope.path.steps[0].name = $scope.path.name;
        }
    };
}