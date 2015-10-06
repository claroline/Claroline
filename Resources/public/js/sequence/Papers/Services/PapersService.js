/**
 * Papers service
 */
(function () {
    'use strict';

    angular.module('PapersApp').factory('PapersService', [
        '$http',
        '$filter',
        '$q',
        function PapersService($http, $filter, $q) {

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
                            console.log('PapersService, getAll method error');
                            console.log(status);
                            console.log(data);
                            deferred.reject([]);
                        });

                    return deferred.promise;
                },
                getOne : function(paperId){
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
                            console.log(status);
                            console.log(data);
                            deferred.reject([]);
                        });

                    return deferred.promise;
                }


            };
        }
    ]);
})();