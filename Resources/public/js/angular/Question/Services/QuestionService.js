/**
 * Question service
 */
angular.module('Question').factory('QuestionService', [
    '$http',
    '$filter',
    '$q',
    '$window',
    'DataSharing',
    function QuestionService($http, $filter, $q, $window, DataSharing) {

        return {
            /**
             * Get an hint
             * @returns promise
             */
            getHint: function (hid) {
                var deferred = $q.defer();
                var paper = DataSharing.getPaper();
                $http
                    .get(
                        Routing.generate('exercice_hint', {paperId: paper.id, hintId: hid})
                    )
                    .success(function (response) {
                        deferred.resolve(response);
                    })
                    .error(function (data, status) {
                        deferred.reject([]);
                        var msg = data && data.error && data.error.message ? data.error.message : 'QuestionService get hint error';
                        var code = data && data.error && data.error.code ? data.error.code : 400;
                        var url = Routing.generate('ujm_sequence_error', {message:msg, code:code});
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
            },
            /**
             * Used for displaying in-context question feedback and solutions
             * @param {type} id question id
             * @returns {$q@call;defer.promise}
             */
            getQuestionSolutions:function(id){
                var deferred = $q.defer();
                $http
                    .get(
                        Routing.generate('get_question_solutions', {id: id})
                    )
                    .success(function (response) {
                        deferred.resolve(response);
                    })
                    .error(function (data, status) {
                        deferred.reject([]);
                        var msg = data && data.error && data.error.message ? data.error.message : 'QuestionService get solutions error';
                        var code = data && data.error && data.error.code ? data.error.code : 400;
                        var url = Routing.generate('ujm_sequence_error', {message:msg, code:code});
                        //$window.location = url;
                    });

                return deferred.promise;
            }

        };
    }
]);