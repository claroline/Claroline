'use strict';

/**
 * Step Modal Controller
 */
var StepModalCtrlProto = [
    '$scope',
    'dialog',
    '$dialog',
    'PathFactory',
    'StepFactory',
    'HistoryFactory',
    'ResourceFactory',
    function($scope, dialog, $dialog, PathFactory, StepFactory, HistoryFactory, ResourceFactory) {
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

        $scope.close = function() {
            dialog.close();
        };

        $scope.save = function(formStep) {
            // Send back edited step to path
            dialog.close(formStep);
        };

        var dialogOptions = {
            backdrop: true,
            keyboard: false,
            backdropClick: false,
        };

        // Resources Management
        $scope.editResource = function(resourceType, resource) {
            var editResource = false;

            // Disable current modal button to prevent close step modal before close document/tool modal
            $scope.buttonsDisabled = true;

            if (undefined != resource && null != resource) {
                editResource = true;
                // Edit existing document
                ResourceFactory.setResource(resource);
            }

            var options = jQuery.extend(true, {}, dialogOptions);

            // Send resource type to form
            options.resolve = {
                resourceType: function() {
                    return resourceType;
                }
            };

            var d = $dialog.dialog(options);
            d.open('partials/modals/resource-edit.html', 'ResourceModalCtrl')
             .then(function(resource) {
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

        $scope.removeResource = function(resource) {
            // Search resource to remove
            for (var i = 0; i < $scope.formStep.resources.length; i++) {
                if (resource.id === $scope.formStep.resources[i].id) {
                    $scope.formStep.resources.splice(i, 1);
                    break;
                }
            }
        };

        $scope.excludeParentResource= function(resource) {
            resource.isExcluded = true;
            $scope.formStep.excludedResources.push(resource.id);

            // Update history
            HistoryFactory.update($scope.path);
        };

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
        
        $scope.selectImage = function() {
            
        };
    }
];