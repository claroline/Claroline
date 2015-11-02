/**
 * Papers service
 */
(function () {
    'use strict';

    angular.module('PapersApp').factory('PapersService', [
        '$http',
        '$filter',
        '$q',
        '$window',
        function PapersService($http, $filter, $q, $window) {

            return {
                getAll: function (exoId) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('ujm_get_exercise_papers', {id: exoId})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error');
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                getOne: function (paperId) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('ujm_get_paper', {id: paperId})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                console.log('PapersService, getOne method error');
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error');
                                $window.location = url;
                            });

                    return deferred.promise;
                }
            };
        }
    ]);
})();