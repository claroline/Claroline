'use strict';

/**
 * Resource Modal Controller
 */
function ResourceModalCtrl($scope, $modalInstance, PathFactory, ResourceFactory, resourceType) {
    $scope.resourceType = resourceType;
    $scope.resourceSubTypes = ResourceFactory.getResourceSubTypes(resourceType);
    
    var currentResource = ResourceFactory.getResource();
    if (null === currentResource) {
        // Create new document
        var newResource = ResourceFactory.generateNewResource();
        newResource.type = resourceType;
        
        $scope.formResource = newResource;
    }
    else {
        // Edit exiting document
        ResourceFactory.setResource(null);
        
        // Create a clone of current document to not affect original data (in case of user click on 'Cancel')
        $scope.formResource = jQuery.extend(true, {}, currentResource);
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
    $scope.save = function(formResource) {
        $modalInstance.close(formResource);
    };
}