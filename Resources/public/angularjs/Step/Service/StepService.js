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

                // Initialize step properties
                this.id                = IdentifierService.generateUUID();
                this.lvl               = lvl;
                this.name              = name;
                this.description       = " ";
                this.children          = [];
                this.activityId        = null;
                this.resourceId        = null;
                this.primaryResource   = null;
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

                        // Primary resources
                        this.addPrimaryResource(step, activity['primaryResource']['type'], activity['primaryResource']['resourceId'], activity['primaryResource']['name']);

                        // Secondary resources
                        if (null !== activity['resources']) {
                            for (var i = 0; i < activity['resources'].length; i++) {
                                var current = activity['resources'][i];

                                this.addSecondaryResource(step, current['type'], current['resourceId'], current['name']);
                            }
                        }

                        // Parameters
                        step.withTutor = activity['withTutor'];
                        step.who       = activity['who'];
                        step.where     = activity['where'];
                        step.duration  = activity['duration'];
                    }
                },

                /**
                 * Set the primary resource of the Step
                 * @param {object} step
                 * @param {string} type
                 * @param {number} id
                 * @param {string} name
                 */
                addPrimaryResource: function (step, type, id, name) {
                    step.primaryResource = new StepResource(type, id, name);
                }
            };
        }
    ]);
})();