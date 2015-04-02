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
                 * Generate a new empty step
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