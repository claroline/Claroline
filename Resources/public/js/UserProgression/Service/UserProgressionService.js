/**
 * User Progression Service
 */
(function () {
    'use strict';

    angular.module('UserProgressionModule').factory('UserProgressionService', [
        '$http',
        '$q',
        'AlertService',
        function PathService($http, $q, AlertService) {
            /**
             * Progression of the current User
             * @type {object}
             */
            var progression = {};

            return {
                get: function get() {
                    return progression;
                },

                set: function set(value) {
                    progression = value;
                },

                create: function create(step) {
                    var deferred = $q.defer();

                    $http
                        .post(Routing.generate('innova_path_progression_create', { id: step.resourceId }))

                        .success(function (response) {
                            deferred.resolve(response);
                        })

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('progression_save_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                update: function update(step, status) {

                }
            }
        }
    ]);
})();