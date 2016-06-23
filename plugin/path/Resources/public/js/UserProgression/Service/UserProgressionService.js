/**
 * User Progression Service
 */
(function () {
    'use strict';

    angular.module('UserProgressionModule').factory('UserProgressionService', [
        '$http',
        '$q',
        'AlertService',
        function UserProgressionService($http, $q, AlertService) {
            /**
             * Progression of the current User
             * @type {object}
             */
            var progression = {};

            /**
             * Current user's total progression for the current path
             * @type {number}
             */
            var totalProgression = 0;

            /**
             * Progression in progress of been updated or created
             * @type {object}
             */
            var inProgress = {};

            return {
                /**
                 * Get User progression for the current Path
                 * @returns {Object}
                 */
                get: function get() {
                    return progression;
                },

                /**
                 * Set User progression for the current Path
                 * @param value
                 */
                set: function set(value) {
                    progression = value;
                },

                /**
                 * Get the User progression for the specified Step
                 * @param step
                 * @returns {Object|null}
                 */
                getForStep : function getForStep(step) {
                    var stepProgression = null;
                    if (angular.isObject(progression) && angular.isObject(progression[step.resourceId])) {
                        stepProgression = progression[step.resourceId];
                    }

                    return stepProgression;
                },

                isStepInProgress: function isStepInProgress(step) {
                  return inProgress[step.resourceId] || false;
                },

                /**
                 * Create a new Progression for the Step
                 * @param step
                 * @param authorized
                 * @param [status]
                 * @returns {object}
                 */
                create: function create(step, status, authorized) {
                    var deferred = $q.defer();
                    //If step is already in update progress or create progress then do nothing
                    if (this.isStepInProgress(step)) {
                      return deferred.promise;
                    }

                    var params = { id: step.resourceId };
                    if (typeof authorized !== 'undefined' && null !== authorized) {
                        params.authorized = authorized;
                    }
                    if (typeof status !== 'undefined' && null !== status && status.length !== 0) {
                        params.status = status;
                    }
                    inProgress[step.resourceId] = true;
                    $http
                        .post(Routing.generate('innova_path_progression_create', params))

                        .success(function (response) {
                            // Store step progression in the Path progression array
                            inProgress[step.resourceId] = false;
                            progression[response.progression.stepId] = response.progression;
                            if (response.totalProgression) {
                              totalProgression = response.totalProgression;
                            }

                            deferred.resolve(response);
                        })

                        .error(function (response) {
                            inProgress[step.resourceId] = false;
                            AlertService.addAlert('error', Translator.trans('progression_save_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Update Progression of the User for a Step
                 * @param step
                 * @param status
                 * @param authorized
                 */
                update: function update(step, status, authorized) {
                    var deferred = $q.defer();

                    authorized = authorized || (progression[step.id].authorized * 1);
                    status = status || progression[step.id].status;
                    inProgress[step.resourceId] = true;
                    $http
                        .put(Routing.generate('innova_path_progression_update', { id: step.resourceId, status: status, authorized: authorized }))

                        .success(function (response) {
                            inProgress[step.resourceId] = false;
                            // Store step progression in the Path progression array
                            if (!angular.isObject(progression[response.progression.stepId])) {
                                progression[response.progression.stepId] = response.progression;
                            } else {
                                progression[response.progression.stepId].status = response.progression.status;
                                progression[response.progression.stepId].authorized = response.progression.authorized;
                            }
                            if (response.totalProgression) {
                              totalProgression = response.totalProgression;
                            }
                            deferred.resolve(response.progression.status);
                        })

                        .error(function (response) {
                            inProgress[step.resourceId] = false;
                            AlertService.addAlert('error', Translator.trans('progression_save_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Get user's total progression for current path
                 *
                 * @returns {number}
                 */
                getTotalProgression: function getTotalProgression() {
                    return totalProgression;
                },

                /**
                 * Set user's total progression for current path
                 * @param value
                 */
                setTotalProgression: function setTotalProgression(value) {
                    totalProgression = value;
                }
            }
        }
    ]);
})();