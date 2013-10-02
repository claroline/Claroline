'use strict';

/**
 * Step Modal Controller
 */
var StepModalCtrlProto = [
    '$scope',
    '$modal',
    '$modalInstance',
    'PathFactory',
    'StepFactory',
    'HistoryFactory',
    'ResourceFactory',
    function($scope, $modal, $modalInstance, PathFactory, StepFactory, HistoryFactory, ResourceFactory) {
        $scope.buttonsDisabled = false;

        var localStep = jQuery.extend(true, {}, StepFactory.getStep()); // Create a copy to not affect original data before user save

        $scope.formStep = localStep;
        $scope.inheritedResources = ResourceFactory.getInheritedResources(localStep);

        $scope.isRootNode = false;
        var path = PathFactory.getPath();
        if (undefined != path.steps[0] && path.steps[0].id == localStep.id) {
            // We are editing root node of tree => diable name field (it has the same name than path)
            $scope.isRootNode = true;
        }

        /**
         * Close step edit
         * @returns void
         */
        $scope.close = function() {
            $modalInstance.dismiss('cancel');
        };

        /**
         * Send back edited step to path
         * @returns void
         */
        $scope.save = function(formStep) {
            $modalInstance.close(formStep);
        };

        
        /**
         * Edit or add resource
         * @returns void
         */
        $scope.editResource = function(resourceType, resource) {
            var editResource = false;

            // Disable current modal button to prevent close step modal before close document/tool modal
            $scope.buttonsDisabled = true;

            if (undefined != resource && null != resource) {
                editResource = true;
                // Edit existing document
                ResourceFactory.setResource(resource);
            }

            var modalInstance = $modal.open({
                templateUrl: EditorApp.webDir + 'js/Resource/Partial/resource-edit.html',
                controller: 'ResourceModalCtrl',
                resolve: {
                    // Send resource type to form
                    resourceType: function() {
                        return resourceType;
                    }
                }
            });

            // Process modal results
            modalInstance.result.then(function(resource) {
                if (resource) {
                    // Save resource
                    if (editResource) {
                        // Edit existing resource
                        // Replace old resource by the new one
                        for (var i = 0; i < $scope.formStep.resources.length; i++) {
                            if ($scope.formStep.resources[i].id === resource.id) {
                                $scope.formStep.resources[i] = resource;
                                break;
                            }
                        }
                    }
                    else {
                        // Create new resource
                        $scope.formStep.resources.push(resource);
                    }
                }

                // Modal is now close, enable buttons
                $scope.buttonsDisabled = false; 
            });
        };

        /**
         * Delete resource from step
         * @returns void
         */
        $scope.removeResource = function(resource) {
            // Search resource to remove
            for (var i = 0; i < $scope.formStep.resources.length; i++) {
                if (resource.id === $scope.formStep.resources[i].id) {
                    $scope.formStep.resources.splice(i, 1);
                    break;
                }
            }
        };

        /**
         * Exclude herited resource from parent step
         * @returns void
         */
        $scope.excludeParentResource= function(resource) {
            resource.isExcluded = true;
            $scope.formStep.excludedResources.push(resource.id);

            // Update history
            HistoryFactory.update($scope.path);
        };

        /**
         * Include herited resource from parent step
         * @returns void
         */
        $scope.includeParentResource= function(resource) {
            resource.isExcluded = false;
            for (var i = 0; i < $scope.previewStep.excludedResources.length; i++) {
                if (resource.id == $scope.previewStep.excludedResources[i]) {
                    $scope.formStep.excludedResources.splice(i, 1);
                }
            }
              
            // Update history
            HistoryFactory.update($scope.path);
        };
        
        /**
         * Select step image in library
         * @returns void
         */
        $scope.selectImage = function() {
            
        };
    }
];