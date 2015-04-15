/**
 * Resource Service
 */
(function () {
    'use strict';

    angular.module('ResourceModule').factory('ResourceService', [
        'IdentifierService',
        'PathService',
        function ResourceService(IdentifierService, PathService) {
            var Resource = function Resource() {
                this.id                  = IdentifierService.generateUUID();
                this.resourceId          = null;
                this.name                = null;
                this.type                = null;
                this.propagateToChildren = true;
            };

            return {
                /**
                 * Generates a new Resource object
                 */
                new: function () {
                    return new Resource();
                },

                /**
                 *
                 * @param stepToFind
                 * @returns object
                 */
                getInheritedResources: function(stepToFind) {
                    var stepFound = false;
                    var inheritedResources = [];

                    var path = PathService.getPath();
                    if (path && path.steps) {
                        for (var i = 0; i < path.steps.length; i++) {
                            var currentStep = path.steps[i];
                            stepFound = this.retrieveInheritedResources(stepToFind, currentStep, inheritedResources);
                            if (stepFound) {
                                break;
                            }
                        }
                    }

                    return inheritedResources;
                },

                /**
                 * @param stepToFind
                 * @param currentStep
                 * @param inheritedResources
                 * @returns boolean
                 */
                retrieveInheritedResources: function(stepToFind, currentStep, inheritedResources) {
                    var stepFound = false;

                    if (stepToFind.id !== currentStep.id && typeof currentStep.children !== 'undefined' && null !== currentStep.children) {
                        // Not the step we search for => search in children
                        for (var i = 0; i < currentStep.children.length; i++) {
                            stepFound = this.retrieveInheritedResources(stepToFind, currentStep.children[i], inheritedResources);
                            if (stepFound) {
                                if (typeof currentStep.resources !== 'undefined' && null !== currentStep.resources) {
                                    // Get all resources which must be sent to children
                                    for (var j = currentStep.resources.length - 1; j >= 0; j--) {
                                        if (currentStep.resources[j].propagateToChildren) {
                                            // Current resource must be available for children
                                            var resource = currentStep.resources[j];
                                            resource.parentStep = {
                                                id: currentStep.id,
                                                lvl: currentStep.lvl,
                                                name: currentStep.name
                                            };
                                            resource.isExcluded = stepToFind.excludedResources.indexOf(resource.id) != -1;
                                            inheritedResources.unshift(resource);
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                    else {
                        stepFound = true;
                    }

                    return stepFound;
                }
            }
        }
    ]);
})();