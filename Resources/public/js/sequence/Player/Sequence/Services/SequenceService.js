/**
 * Exercise player service
 */
(function () {
    'use strict';

    angular.module('Sequence').factory('SequenceService', [
        '$http',
        '$filter',
        '$q',
        '$window',
        function SequenceService($http, $filter, $q, $window) {

            return {
                /**
                 * Save the answer given to a question
                 * @param {number} paperId
                 * @param {object} answer
                 * @returns promise
                 */
                submitAnswer: function (paperId, studentData) {
                    var deferred = $q.defer();
                    $http
                            .put(
                                    
                                    Routing.generate('exercise_submit_answer', {paperId: paperId, questionId: studentData.question.id}), {data: studentData.answers}

                            )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                console.log(data);
                                var url = Routing.generate('ujm_sequence_error', {message:data.error.message, code:data.error.code});
                                $window.location = url;
                            });
                    return deferred.promise;
                },
                /**
                 * End the sequence by setting the paper data
                 * @param {integer} exoId
                 * @param {object} studentPaper
                 * @param {bool} interrupted
                 * @returns promise
                 */
                endSequence: function (studentPaper) {
                    var deferred = $q.defer();
                    $http
                            .put(
                                    //finish_paper    
                                    Routing.generate('exercise_finish_paper', {id: studentPaper.id})
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
                }
            };
        }
    ]);
})();