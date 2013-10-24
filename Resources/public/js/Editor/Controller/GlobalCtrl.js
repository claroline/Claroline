'use strict';

/**
 * Global Controller
 */
function GlobalCtrl($scope, $http, HistoryFactory, PathFactory) {
    $scope.initPath(PathFactory.getPath());
    
    // Hide templates (it's only used in scenario with the tree view)
    $scope.templateSidebar.show = false;
    
    /**
     * Check if path name is unique for current user and current workspace
     */
    $scope.checkNameIsUnique = function() {
//        $http({
//            method: '',
//            url: route,
//            data: data
//        })
//        .success(function (data) {
//            
//        });
        
//        $http.get(Routing.generate('innova_path_get_path', {id: EditorApp.pathId}))
//        .success(function (data) {
//           path = data;
//           return deferred.resolve(path);
//        })
//        .error(function(data, status) {
//            return deferred.reject('error loading path');
//        });
    };
    
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