/**
 * Step Service
 */
(function () {
    'use strict';

    angular.module('StepModule').factory('StepService', [
        'IdentifierService',
        'ResourceService',
        function StepService(IdentifierService, ResourceService) {
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
                    name = Translator.trans('root_default_name', {}, 'path_wizards');
                }

                // Initialize step properties
                this.id                = IdentifierService.generateUUID();
                this.lvl               = lvl;
                this.name              = name;
                this.description       = " ";
                this.children          = [];
                this.activityId        = null;
                this.resourceId        = null;
                this.primaryResource   = [];
                this.resources         = [];
                this.excludedResources = [];
                this.withTutor         = false;
                this.who               = null;
                this.where             = null;
                this.duration          = null;
            };

            /**
             * Resource object
             * @param {string} [type]
             * @param {number} [id]
             * @param {string} [name]
             * @constructor
             */
            var StepResource = function StepResource(type, id, name) {
                // Initialize resource properties
                this.id                  = IdentifierService.generateUUID();
                this.resourceId          = id ? id : null;
                this.name                = name ? name : null;
                this.type                = type ? type : null;
                this.propagateToChildren = true;
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
                 * Load Activity data from ID
                 * @param {object} step
                 * @param {number} activityId
                 */
                loadActivity: function (step, activityId) {
                    $http.get(Routing.generate('innova_path_load_activity', { nodeId: activityId }))
                        .success(function (data) {
                            this.setActivity(step, data);
                        }.bind(this));
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
                        step.primaryResource = [];
                        step.resources = [];

                        // Initialize a new Resource object (parameters : claro type, mime type, id, name)
                        var primaryResource = ResourceService.new(activity['primaryResource']['type'], activity['primaryResource']['mimeType'], activity['primaryResource']['resourceId'], activity['primaryResource']['name']);
                        this.addResource(step.primaryResource, primaryResource);

                        // Secondary resources
                        if (null !== activity['resources']) {
                            for (var i = 0; i < activity['resources'].length; i++) {
                                var current = activity['resources'][i];

                                var resource = ResourceService.new(current['type'], current['mimeType'], current['resourceId'], current['name']);
                                this.addResource(step.resources, resource);
                            }
                        }

                        // Parameters
                        step.withTutor = activity['withTutor'];
                        step.who       = activity['who'];
                        step.where     = activity['where'];
                        step.duration  = activity['duration'];
                    }
                },

                addResource: function (resourcesList, resource) {
                    if (!ResourceService.exists(resourcesList, resource)) {
                        // Resource is not in the list => add it
                        resourcesList.push(resource);
                    }
                }
            };
        }
    ]);
})();