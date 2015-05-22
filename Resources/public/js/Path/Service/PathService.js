/**
 * Path Service
 */
(function () {
    'use strict';

    angular.module('PathModule').factory('PathService', [
        '$http',
        '$q',
        '$timeout',
        '$location',
        'AlertService',
        'StepService',
        function PathService($http, $q, $timeout, $location, AlertService, StepService) {
            /**
             * ID of the Path
             * @type {Number}
             */
            var id = null;

            /**
             * Data of the Path
             * @type {object}
             */
            var path = null;

            // Do not allow adding children to steps at the max depth
            var maxDepth = 8;

            return {
                /**
                 * Get ID of the current Path
                 * @returns {Number}
                 */
                getId: function () {
                    return id;
                },

                /**
                 * Set ID of the current Path
                 * @param value
                 */
                setId: function (value) {
                    id = value;
                },

                /**
                 * Get current Path
                 * @returns {Object}
                 */
                getPath: function () {
                    return path;
                },

                /**
                 * Set current Path
                 * @param value
                 */
                setPath: function (value) {
                    path = value;
                },

                /**
                 * Initialize a new Path structure
                 */
                initialize: function () {
                    // Create a generic root step
                    var rootStep = StepService.new();

                    path.structure.push(rootStep);

                    // Set root step as current step
                    this.goTo(rootStep);
                },

                /**
                 * Initialize a new Path structure from a Template
                 */
                initializeFromTemplate: function () {

                },

                /**
                 * Save modification to DB
                 */
                save: function () {
                    // Transform data to make it acceptable by Symfony
                    var dataToSave = {
                        innova_path: {
                            name:        path.name,
                            description: path.description,
                            breadcrumbs: path.breadcrumbs,
                            structure:   angular.toJson(path)
                        }
                    };

                    var deferred = $q.defer();

                    $http
                        .put(Routing.generate('innova_path_editor_wizard_save', { id: id }), dataToSave)

                        .success(function (response) {
                            if ('ERROR_VALIDATION' === response.status) {
                                for (var i = 0; i < response.messages.length; i++) {
                                    AlertService.addAlert('error', response.messages[i]);
                                }

                                deferred.reject(response);
                            } else {
                                // Get updated data
                                angular.copy(response.data, path);

                                // Display confirm message
                                AlertService.addAlert('success', Translator.trans('path_save_success', {}, 'path_wizards'));

                                deferred.resolve(response);
                            }
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('path_save_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Publish path modifications
                 */
                publish: function () {
                    var deferred = $q.defer();

                    $http
                        .put(Routing.generate('innova_path_publish', { id: id }))

                        .success(function (response) {
                            if ('ERROR' === response.status) {
                                for (var i = 0; i < response.messages.length; i++) {
                                    AlertService.addAlert('error', response.messages[i]);
                                }

                                deferred.reject(response);
                            } else {
                                // Get updated data
                                angular.copy(response.data, path);

                                // Display confirm message
                                AlertService.addAlert('success', Translator.trans('publish_success', {}, 'path_wizards'));

                                deferred.resolve(response);
                            }
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('publish_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Display the step
                 * @param step
                 */
                goTo: function goTo(step) {
                    // Ugly as fuck, but can't make it work without timeout
                    $timeout(function(){
                        if (angular.isObject(step)) {
                            $location.path('/' + step.id);
                        } else {
                            $location.path('/');
                        }
                    }, 1);
                },

                /**
                 * Get the previous step
                 * @param step
                 * @returns {Object|Step}
                 */
                getPrevious: function (step) {
                    var previous = null;

                    return previous;
                },

                /**
                 * Get the next step
                 * @param step
                 * @returns {Object|Step}
                 */
                getNext: function getNext(step) {
                    var next = null;

                    return next;
                },

                /**
                 * Loop over all steps of path and execute callback
                 * Iteration stops when callback returns true
                 * @param {array}    steps    - an array of steps to browse
                 * @param {function} callback - a callback to execute on each step (called with args `parentStep`, `currentStep`)
                 */
                browseSteps: function (steps, callback) {
                    /**
                     * Recursively loop through the steps to execute callback on each step
                     * @param   {object} parentStep
                     * @param   {object} currentStep
                     * @returns {boolean}
                     */
                    function recursiveLoop(parentStep, currentStep) {
                        var terminated = false;

                        // Execute callback on current step
                        if (typeof callback === 'function') {
                            terminated = callback(parentStep, currentStep);
                        }

                        if (!terminated && typeof currentStep.children !== 'undefined' && currentStep.children.length !== 0) {
                            for (var i = 0; i < currentStep.children.length; i++) {
                                terminated = recursiveLoop(currentStep, currentStep.children[i]);
                            }
                        }

                        return terminated;
                    }

                    if (typeof steps !== 'undefined' && steps.length !== 0) {
                        for (var j = 0; j < steps.length; j++) {
                            var terminated = recursiveLoop(null, steps[j]);
                            if (terminated) {
                                break;
                            }
                        }
                    }
                },

                /**
                 * Recalculate steps level in tree
                 * @param {array} steps - an array of steps to reorder
                 */
                reorderSteps: function (steps) {
                    this.browseSteps(steps, function (parent, step) {
                        if (null !== parent) {
                            step.lvl = parent.lvl + 1;
                        } else {
                            step.lvl = 0;
                        }
                    });
                },

                addStep: function (parent, displayNew) {
                    if (parent.lvl < maxDepth) {
                        // Create a new step
                        var step = StepService.new(parent);

                        if (displayNew) {
                            // Open created step
                            this.goTo(step);
                        }
                    }
                },

                /**
                 * Remove a step from the path's tree
                 * @param {array}  steps        - an array of steps to browse
                 * @param {object} stepToDelete - the step to delete
                 */
                removeStep: function (steps, stepToDelete) {
                    this.browseSteps(steps, function (parent, step) {
                        var deleted = false;
                        if (step === stepToDelete) {
                            if (typeof parent !== 'undefined' && null !== parent) {
                                var pos = parent.children.indexOf(stepToDelete);
                                if (-1 !== pos) {
                                    parent.children.splice(pos, 1);

                                    deleted = true;
                                }
                            } else {
                                // We are deleting the root step
                                var pos = steps.indexOf(stepToDelete);
                                if (-1 !== pos) {
                                    steps.splice(pos, 1);

                                    deleted = true;
                                }
                            }
                        }

                        return deleted;
                    });
                },

                getStep: function (stepId) {
                    var step = null;

                    if (path) {
                        this.browseSteps(path.steps, function searchStep(parent, current) {
                            if (current.id == stepId) {
                                step = current;

                                return true; // Kill the search
                            }

                            return false;
                        });
                    }

                    return step;
                },

                getStepInheritedResources: function (steps, step) {
                    function retrieveInheritedResources(stepToFind, currentStep, inheritedResources) {
                        var stepFound = false;

                        if (stepToFind.id !== currentStep.id && typeof currentStep.children !== 'undefined' && null !== currentStep.children) {
                            // Not the step we search for => search in children
                            for (var i = 0; i < currentStep.children.length; i++) {
                                stepFound = retrieveInheritedResources(stepToFind, currentStep.children[i], inheritedResources);
                                if (stepFound) {
                                    if (typeof currentStep.resources !== 'undefined' && null !== currentStep.resources) {
                                        // Get all resources which must be sent to children
                                        for (var j = currentStep.resources.length - 1; j >= 0; j--) {
                                            if (currentStep.resources[j].propagateToChildren) {
                                                // Current resource must be available for children
                                                var resource = angular.copy(currentStep.resources[j]);
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

                    var stepFound = false;
                    var inheritedResources = [];

                    if (steps && steps.length !== 0) {
                        for (var i = 0; i < steps.length; i++) {
                            var currentStep = steps[i];
                            stepFound = retrieveInheritedResources(step, currentStep, inheritedResources);
                            if (stepFound) {
                                break;
                            }
                        }
                    }

                    return inheritedResources;
                }
            }
        }
    ]);
})();