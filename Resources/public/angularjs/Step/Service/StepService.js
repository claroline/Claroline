/**
 * Step Service
 */
(function () {
    'use strict';

    angular.module('StepModule').factory('StepService', [
        'IdentifierService',
        function StepService(IdentifierService) {
            /**
             * Step object
             * @constructor
             */
            var Step = function Step(parent) {
                this.id                = IdentifierService.generateUUID();
                this.lvl               = parent.lvl + 1;
                this.name              = 'Step ' + this.lvl + '.' + (parent.children.length + 1);
                this.children          = [];
                this.resources         = [];
                this.excludedResources = [];
            };

            return {
                /**
                 * Generate a new empty step
                 *
                 * @param   {object} parentStep
                 * @returns {Step}
                 */
                addNewChild: function (parentStep) {
                    var newStep = new Step(parentStep);

                    // Append new child to his parent
                    if (!parentStep.children instanceof Array) {
                        parentStep.children = [];
                    }
                    parentStep.children.push(newStep);

                    return newStep;
                },

                /**
                 * Search resource in path and replace it by a new one
                 *
                 * @param   {object} newResource
                 * @returns {StepService}
                 */
                replaceResource: function (newResource) {
                    if (null !== step && typeof step.resources !== 'undefined' && null !== step.resources) {
                        for (var i = 0; i < step.resources.length; i++) {
                            if (newResource.id === step.resources[i].id) {
                                this.updateResource(oldResource, newResource);
                                break;
                            }
                        }
                    }

                    return this;
                },

                /**
                 * Update resource properties
                 *
                 * @param   {object} oldResource
                 * @param   {object} newResource
                 * @returns {StepService}
                 */
                updateResource: function (oldResource, newResource) {
                    for (var prop in newResource) {
                        oldResource[prop] = newResource[prop];
                    }

                    return this;
                },

                /**
                 * Remove selected resource from step
                 *
                 * @param   {object} step       current step
                 * @param   {string} resourceId resource to remove
                 * @returns {StepService}
                 */
                removeResource: function (step, resourceId) {
                    // Remove from included resources
                    if (typeof step.resources !== 'undefined' && null !== step.resources) {
                        for (var i = 0; i < step.resources.length; i++) {
                            if (resourceId === step.resources[i].id) {
                                step.resources.splice(i, 1);
                                break;
                            }
                        }
                    }

                    // Remove from excluded resources
                    if (typeof step.excludedResources != 'undefined' && null !== step.excludedResources) {
                        // Loop through excluded resource to remove reference to needle
                        for (var j = 0; j < step.excludedResources.length; j++) {
                            if (resourceId == step.excludedResources[j]) {
                                step.excludedResources.splice(j, 1);
                                break;
                            }
                        }
                    }

                    // Loop through children to remove propagation of the deleted resource
                    if (typeof step.children != 'undefined' && null !== step.children) {
                        for (var k = 0; k < step.children.length; k++) {
                            this.removeResource(step.children[k], resourceId);
                        }
                    }

                    return this;
                },

                /**
                 * Check if a step contains a resource
                 * @param {object} step
                 * @param {number} resourceId
                 */
                hasResource: function (step, resourceId) {
                    var resourceExists = false;
                    for (var i = 0; i < step.resources.length; i++) {
                        var res = step.resources[i];
                        if (res.resourceId === resourceId) {
                            resourceExists = true;
                            break;
                        }
                    }

                    return resourceExists;
                }
            };
        }
    ]);
})();