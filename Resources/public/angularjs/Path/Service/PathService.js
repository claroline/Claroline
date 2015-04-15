/**
 * Path Service
 */
(function () {
    'use strict';

    angular.module('PathModule').factory('PathService', [
        '$http',
        'AlertService',
        function PathService($http, AlertService) {
            return {
                /**
                 * Save modification to DB
                 * @param {number} id
                 * @param {{}}     path
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

                    // Call server
                    return $http
                        .put(Routing.generate('innova_path_editor_wizard_save', { id: id }), dataToSave)

                        .success(function (response) {
                            if ('ERROR_VALIDATION' === response.status) {
                                for (var i = 0; i < response.messages.length; i++) {
                                    AlertService.addAlert('error', response.messages[i]);
                                }
                            } else {
                                // Get updated data
                                angular.copy(response.data, path);

                                // Display confirm message
                                AlertService.addAlert('success', Translator.trans('path_save_success', {}, 'path_editor'));
                            }
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('path_save_error', {}, 'path_editor'));
                        });
                },

                /**
                 * Publish path modifications
                 * @param {number} id
                 */
                publish: function (id) {

                },

                /**
                 * Loop over all steps of path and execute callback
                 * Iteration stops when callback returns true
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

                reorderSteps: function (steps) {
                    this.browseSteps(steps, function (parent, step) {
                        if (null !== parent) {
                            step.lvl = parent.lvl + 1;
                        } else {
                            step.lvl = 0;
                        }
                    });
                },

                removeStep: function (steps, stepToDelete) {
                    this.browseSteps(steps, function (parent, step) {
                        var deleted = false;
                        if (step === stepToDelete) {
                            console.log('found');
                            var pos = parent.children.indexOf(stepToDelete);
                            if (-1 !== pos) {
                                console.log('deleted');
                                parent.children.splice(pos, 1);

                                deleted = true;
                            }
                        }

                        return deleted;
                    });
                }
            };
        }
    ]);
})();