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
        'CommonService',
        function QuestionService($http, $filter, $q, $window, CommonService) {

            return {
                /**
                 * Get an hint
                 * @returns promise
                 */
                getHint: function (hid) {
                    var deferred = $q.defer();
                    var paper = CommonService.getPaper();
                    $http
                            .get(
                                    //Routing.generate('ujm_get_hint_content', {id: _id})
                                    Routing.generate('exercice_hint', {paperId: paper.id, hintId: hid})
                                    //exercice_hint
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);                               
                                var url = Routing.generate('ujm_sequence_error', {message:data.error.message, code:data.error.code});
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