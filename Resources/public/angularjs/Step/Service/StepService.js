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
                var name, lvl;

                if (parent) {
                    lvl  = parent.lvl + 1;
                    name = 'Step ' + lvl + '.' + (parent.children.length + 1);
                } else {
                    lvl = 0;
                    name = Translator.trans('root_default_name', {}, 'path_editor');
                }

                this.id                = IdentifierService.generateUUID();
                this.lvl               = lvl;
                this.name              = name;
                this.children          = [];
                this.resources         = [];
                this.excludedResources = [];
            };

            return {
                /**
                 * Generates a new empty step
                 *
                 * @param   {object} [parentStep]
                 * @returns {Step}
                 */
                new: function (parentStep) {
                    var newStep = new Step(parentStep);

                    if (parentStep) {
                        // Append new child to its parent
                        if (!parentStep.children instanceof Array) {
                            parentStep.children = [];
                        }
                        parentStep.children.push(newStep);
                    }

                    return newStep;
                },

                /**
                 * Injects the Activity data into step
                 * @param step
                 * @param activity
                 */
                setActivity: function (step, activity) {
                    if (typeof activity !== 'undefined' && activity !== null && activity.length !== 0) {
                        // Populate step
                        step.activityId  = activity['id'];
                        step.name        = activity['name'];
                        step.description = activity['description'];

                        // Primary resources
                        step.primaryResource = activity['primaryResource'];

                        // Secondary resources
                        if (null !== activity['resources']) {
                            for (var i = 0; i < activity['resources'].length; i++) {
                                var resource = activity['resources'][i];
                                var resourceExists = this.hasResource(step, resource.resourceId);
                                if (!resourceExists) {
                                    // Generate new local ID
                                    resource['id'] = PathService.getNextResourceId();

                                    // Add to secondary resources
                                    step.resources.push(resource);
                                }
                            }
                        }

                        // Parameters
                        step.withTutor = activity['withTutor'];
                        step.who       = activity['who'];
                        step.where     = activity['where'];
                        step.duration  = activity['duration'];
                    }
                },

                addResource: function (step, resource) {
                    if (this.hasResource(step, resource)) {

                    }
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
                 * @param {object} resource
                 */
                hasResource: function (step, resource) {
                    var resourceExists = false;
                    for (var i = 0; i < step.resources.length; i++) {
                        var stepResource = step.resources[i];
                        if (stepResource.resourceId === resource.resourceId) {
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