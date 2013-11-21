'use strict';

/**
 * Resource Modal Controller
 */
function ResourcePickerModalCtrl($scope, $modal, $http, $q, $modalInstance, PathFactory, ResourceFactory, resources, currentResourceId) {
    $scope.resources = resources;
    $scope.currentResourceId = currentResourceId;

    // Translate resource types
    if ($scope.resources.length !== 0) {
        for (var i = 0; i < $scope.resources.length; i++) {
            if (Translator.has('Resource:' + $scope.resources[i].type)) {
                $scope.resources[i].type = Translator.get('Resource:' + $scope.resources[i].type);
            }
        }
    }
    
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