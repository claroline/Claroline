/**
 * Question service
 */
(function () {
    'use strict';

    angular.module('Question').factory('QuestionService', [
        '$http',
        '$filter',
        '$q',
        '$window',
        function QuestionService($http, $filter, $q, $window) {

            return {
                /**
                 * Get an hint
                 * @returns promise
                 */
                getHint: function (_id) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                Routing.generate('ujm_get_hint_content', {id: _id})
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
                /**
                 * Get a hint penalty
                 * @param {type} collection array of penalty
                 * @param {type} searched searched id
                 * @returns number
                 */
                getHintPenalty: function (collection, searched) {
                    for (var i = 0; i < collection.length; i++) {
                        if (collection[i].id === searched) {
                            return collection[i].penalty;
                        }
                    }
                }

            };
        }
    ]);
})();