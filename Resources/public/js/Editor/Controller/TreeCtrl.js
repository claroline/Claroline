'use strict';

/**
 * Tree Controller
 */
function TreeCtrl($scope, $modal, HistoryFactory, PathFactory, StepFactory, ResourceFactory) {
    $scope.initPath(PathFactory.getPath());
    
    $scope.previewStep = null;
    
    $scope.sortableOptions = {
        update: function(e, ui) { $scope.applyTreeChanges(); },
        placeholder: 'placeholder',
        connectWith: '.ui-sortable'
    };
    
    /**
     * 
     * @returns void
     */
    $scope.setPreviewStep = function(step) {
        var isRootStep = false;
        var rootStep = null;
        if (undefined !== $scope.path && null !== $scope.path && undefined !== $scope.path.steps[0]) {
            rootStep = $scope.path.steps[0];
        }
        
        if (step) {
            $scope.previewStep = step;
            if (step.id === rootStep.id) {
                isRootStep = true;
            }
        }
        else if (rootStep) {
            $scope.previewStep = rootStep;
            isRootStep = true;
        }
        
        $scope.stepIsRootNode = isRootStep;
        $scope.inheritedResources = ResourceFactory.getInheritedResources($scope.previewStep);
    };
    
    if (null === $scope.previewStep) {
        $scope.setPreviewStep();
    }
    
    /**
     * Reload preview step to apply last changes
     * @returns void
     */
    $scope.updatePreviewStep = function() {
        // Update preview step
        var step = null;
        if (null !== $scope.previewStep) {
            step = PathFactory.getStepById($scope.previewStep.id);
        }
        $scope.setPreviewStep(step);
    };
    
    /**
     * Update Path when Tree is modified with drag n drop
     * @returns void
     */
    $scope.applyTreeChanges = function() {
        var e, i, _i, _len, _ref;
        _ref = $scope.path;
        for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
            e = _ref[i];
            e.pos = i;
        }
        HistoryFactory.update(_ref);
        $scope.updatePreviewStep();
    };
     
    /**
     * Remove a step from Tree
     * @returns void
     */
    $scope.remove = function(step) {
        // Search step to remove
        function walk(path) {
            var children = path.children;
            var i;

            if (children) {
                i = children.length;
                while (i--) {
                    if (children[i] === step) {
                        return children.splice(i, 1);
                    } else {
                        walk(children[i]);
                    }
                }
            }
        }
         
        walk($scope.path.steps[0]);
         
        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };
     
    /**
     * Remove all children of the specified step
     * @returns void
     */
    $scope.removeChildren = function(step) {
        step.children = [];
        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };

    /**
     * Add a new step child to specified step
     * @returns void
     */
    $scope.addChild = function(step) {
        var newStep = StepFactory.generateNewStep(step);
        step.children.push(newStep);
        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };
     
    /**
     * 
     * @returns void
     */
    $scope.addSibling = function(step) {
        var newStep = StepFactory.generateNewStep(step);
         
        function insertStep(steps) {
            var stepInserted = false;
            for (var i = 0; i < steps.length; i++) {
                if (steps[i].id === step.id) {
                    steps.splice(i+1, 0, newStep);
                    stepInserted = true;
                }
                else {
                    stepInserted = insertStep(steps[i].children);
                }
                 
                if (stepInserted) {
                    break;
                }
            }
            return stepInserted;
        }

        insertStep($scope.path.steps);
        
        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };
     
    /**
     * Open modal to create a new template from specified step(s)
     * @returns void
     */
    $scope.editTemplate = function(step) {
        StepFactory.setStep(step);
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'js/Template/Partial/template-edit.html',
            controller: 'TemplateModalCtrl'
        });
    };
     
    /**
     * Open modal to modify specified step properties
     * @returns void
     */
    $scope.editStep = function(step) {
        StepFactory.setStep(step);
        
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'js/Step/Partial/step-edit.html',
            controller: 'StepModalCtrl',
            windowClass: 'step-edit'
        });

        // Process modal results
        modalInstance.result.then(function(step) {
            if (step) {
                // Inject edited step in path
                PathFactory.replaceStep(step);
                
                // Update history
                HistoryFactory.update($scope.path);
                $scope.updatePreviewStep();
            }
        });
    };
    
    /**
     * 
     * @returns void
     */
    $scope.editResource = function(resourceType, resource) {
        var editResource = false;
        
        if (resource) {
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
                    for (var i = 0; i < $scope.previewStep.resources.length; i++) {
                        if ($scope.previewStep.resources[i].id === resource.id) {
                            $scope.previewStep.resources[i] = resource;
                            break;
                        }
                    }
                }
                else {
                    // Create new resource
                    $scope.previewStep.resources.push(resource);
                }
            
                // Update history
                HistoryFactory.update($scope.path);
            }
        });
    };
    
    /**
     * 
     * @returns void
     */
    $scope.removeResource = function(resource) {
        // Search resource to remove
        for (var i = 0; i < $scope.previewStep.resources.length; i++) {
            if (resource.id === $scope.previewStep.resources[i].id) {
                $scope.previewStep.resources.splice(i, 1);
                
                // Loop through
                
                
                // Update history
                HistoryFactory.update($scope.path);
                break;
            }
        }
    };
     
    /**
     * 
     * @returns void
     */
    $scope.excludeParentResource= function(resource) {
        resource.isExcluded = true;
        $scope.previewStep.excludedResources.push(resource.id);
        
        // Update history
        HistoryFactory.update($scope.path);
    };
    
    /**
     * 
     * @returns void
     */
    $scope.includeParentResource= function(resource) {
        resource.isExcluded = false;
        for (var i = 0; i < $scope.previewStep.excludedResources.length; i++) {
            if (resource.id == $scope.previewStep.excludedResources[i]) {
                $scope.previewStep.excludedResources.splice(i, 1);
            }
        }
        
         // Update history
         HistoryFactory.update($scope.path);
    };
}