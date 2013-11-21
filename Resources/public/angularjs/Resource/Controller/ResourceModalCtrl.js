'use strict';

/**
 * Resource Modal Controller
 */
function ResourceModalCtrl($scope, $modal, $q, $http, $modalInstance, PathFactory, ResourceFactory, resourceType) {
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

    /**
     * Edit or add resource
     * @returns void
     */
    $scope.pickResource = function(currentResourceId) {
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'angularjs/Resource/Partial/resource-picker.html',
            controller: 'ResourcePickerModalCtrl',
            resolve: {
                currentResourceId: function() {
                    return undefined != typeof(currentResourceId) && null != currentResourceId && 0 != currentResourceId.length ? currentResourceId : null;
                },
                
                // Send resource type to form
                resources: function() {
                    var deferred = $q.defer();
                    $http.get(Routing.generate("innova_user_resources"))
                         .success(function (data) {
                             var resources = data;
                             return deferred.resolve(resources);
                         })
                         .error(function(data, status) {
                             return deferred.reject('error loading resources');
                         });

                    return deferred.promise;
                }
            }
        });
        
        // Process modal results
        modalInstance.result.then(function(resourcePicked) {
            $scope.formResource.resourceId = resourcePicked.id;
            $scope.formResource.name = resourcePicked.name;
        });
    };
}