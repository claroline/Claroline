/**
 * Path Service
 */
(function () {
    'use strict';

    angular.module('PathModule').factory('PathService', [
        '$http',
        '$q',
        'AlertService',
        function PathService($http, $q, AlertService) {
            return {
                /**
                 * Save modification to DB
                 * @param {number} id   - ID of the path
                 * @param {object} path - data of the path
                 */
                save: function (id, path) {
                    // Transform data to make it acceptable by Symfony
                    var dataToSave = {
                        innova_path: {
                            name:        path.name,
                            description: path.description,
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
                                AlertService.addAlert('success', Translator.trans('path_save_success', {}, 'path_editor'));

                                deferred.resolve(response);
                            }
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('path_save_error', {}, 'path_editor'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Publish path modifications
                 * @param {number} id
                 * @param {object} path
                 */
                publish: function (id, path) {
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
                                AlertService.addAlert('success', Translator.trans('publish_success', {}, 'path_editor'));

                                deferred.resolve(response);
                            }
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('publish_error', {}, 'path_editor'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
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

                /**
                 * Remove a step from the path's tree
                 * @param {array}  steps        - an array of steps to browse
                 * @param {object} stepToDelete - the step to delete
                 */
                removeStep: function (steps, stepToDelete) {
                    this.browseSteps(steps, function (parent, step) {
                        var deleted = false;
                        if (step === stepToDelete) {
                            var pos = parent.children.indexOf(stepToDelete);
                            if (-1 !== pos) {
                                parent.children.splice(pos, 1);

                                deleted = true;
                            }
                        }

                        return deleted;
                    });
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