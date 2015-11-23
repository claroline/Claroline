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
                    //console.log(answer);
                    $http
                            .put(
                                    // Routing.generate('ujm_sequence_submit_answer', {paperId : paperId, questionId : answer.question.id}), {data: answer}
                                    Routing.generate('exercise_submit_answer', {paperId: paperId, questionId: studentData.question.id}), {data: studentData.answers}

                            )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                        console.log(data);
                                var url = Routing.generate('ujm_sequence_error');
                                //$window.location = url;
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
                                    //Routing.generate('ujm_sequence_end', {id: exoId}), {paper: studentPaper, interrupted : interrupted}
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                        console.log(data);
                                var url = Routing.generate('ujm_sequence_error');
                                //$window.location = url;
                            });
                    return deferred.promise;
                },
                /**
                 * Get an exercise
                 * @param {type} id
                 * @returns {$q@call;defer.promise}
                 */
                get: function (id) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    //exercise_papers        
                                    Routing.generate('exercise_get', {id: id})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                        console.log(data);
                                var url = Routing.generate('ujm_sequence_error');
                                //$window.location = url;
                            });

                    return deferred.promise;
                }
            };
        }
    ]);
})();