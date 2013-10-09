'use strict';

/**
 * Resource Modal Controller
 */
function ResourcePickerModalCtrl($scope, $modal, $http, $q, $modalInstance, PathFactory, ResourceFactory, resources) {
    $scope.resources = resources;
    $scope.resourcePicked = null; 

    /**
     * Close resource edit
     * @returns void
     */
    $scope.close = function() {
        $modalInstance.dismiss('cancel');
    };
    
    /**
     * Send back edited document to step
     * @returns void
     */
    $scope.save = function(resourcePicked) {
        $modalInstance.close(resourcePicked);
    };



}