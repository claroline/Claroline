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
                 * @returns {object}
                 */
                create: function create(step) {
                    var deferred = $q.defer();

                    $http
                        .post(Routing.generate('innova_path_progression_create', { id: step.resourceId }))

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
                 */
                update: function update(step, status) {

                }
            }
        }
    ]);
})();