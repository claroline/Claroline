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

                /**
                 * Create a new Progression for the Step
                 * @param step
                 * @param authorized
                 * @param [status]
                 * @returns {object}
                 */
                create: function create(step, status, authorized) {
                    var deferred = $q.defer();

                    var params = { id: step.resourceId };
                    if (typeof authorized !== 'undefined' && null !== authorized) {
                        params.authorized = authorized;
                    }
                    if (typeof status !== 'undefined' && null !== status && status.length !== 0) {
                        params.status = status;
                    }
                    $http
                        .post(Routing.generate('innova_path_progression_create', params))

                        .success(function (response) {
                            // Store step progression in the Path progression array
                            progression[response.stepId] = response;

                            deferred.resolve(response);
                        })

                        .error(function (response) {
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
                    $http
                        .put(Routing.generate('innova_path_progression_update', { id: step.resourceId, status: status, authorized: authorized }))

                        .success(function (response) {
                            // Store step progression in the Path progression array
                            if (!angular.isObject(progression[response.stepId])) {
                                progression[response.stepId] = response;
                            } else {
                                progression[response.stepId].status = response.status;
                                progression[response.stepId].authorized = response.authorized;
                            }
                            deferred.resolve(response.status);
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('progression_save_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                }
            }
        }
    ]);
})();