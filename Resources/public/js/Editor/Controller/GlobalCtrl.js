'use strict';

/**
 * Global Controller
 */
function GlobalCtrl($scope, HistoryFactory, PathFactory) {
    $scope.initPath(PathFactory.getPath());
    
    // Hide templates (it's only used in scenario with the tree view)
    $scope.templateSidebar.show = false;
    
    /**
     * Update Root step when path name changes
     * 
     * @returns void
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