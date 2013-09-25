'use strict';

/**
 * Resource Modal Controller
 */
var ResourceModalCtrlProto = [
    '$scope',
    'dialog',
    'PathFactory',
    'ResourceFactory',
    'resourceType',
    function($scope, dialog, PathFactory, ResourceFactory, resourceType) {
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
        
        $scope.close = function() {
            dialog.close();
        };
        
        $scope.save = function(formResource) {
            // Send back edited document to step
            dialog.close(formResource);
        };
    }
];