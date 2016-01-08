/**
 * Exercise player service
 */
(function () {
    'use strict';

    angular.module('ExercisePlayerApp').factory('ExerciseService', [
        '$http',
        '$filter',
        '$q',
        '$window',
        function ExerciseService($http, $filter, $q, $window) {

            return {
                
                getExercise: function(id){
                    var deferred = $q.defer();
                    $http.post(
                        Routing.generate('exercise_new_attempt', {id: id})
                    ).success(function(response){
                        deferred.resolve(response);
                    }).error(function(data, status){
                        deferred.reject([]);
                        var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService get exercise error';
                        var code = data && data.error && data.error.code ? data.error.code : 403;
                        var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
                        $window.location = url;
                    });
                    return deferred.promise;
                },
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
                                var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService submit answer error';
                                var code = data && data.error && data.error.code ? data.error.code : 403;
                                var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
                                console.log('error');
                                console.log(data);
                                console.log(status);
                                // $window.location = url;
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
                                var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService end sequence error';
                                var code = data && data.error && data.error.code ? data.error.code : 403;
                                var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
                                $window.location = url;
                            });
                    return deferred.promise;
                },
                /**
                 * shuffle array elements
                 * @param {array} the given array
                 * @returns {array} the shuffled array
                 */
                shuffleArray: function (array) {
                    var currentIndex = array.length, temporaryValue, randomIndex;
                    // While there remain elements to shuffle...
                    while (0 !== currentIndex) {
                        // Pick a remaining element...
                        randomIndex = Math.floor(Math.random() * currentIndex);
                        currentIndex -= 1;

                        // And swap it with the current element.
                        temporaryValue = array[currentIndex];
                        array[currentIndex] = array[randomIndex];
                        array[randomIndex] = temporaryValue;
                    }
                    return array;
                },
                /**
                 * @param {object} object a javascript object with meta property
                 * @returns null or string
                 */
                objectHasOtherMeta: function (object) {
                    if (!object.meta || object.meta === undefined || object.meta === 'undefined') {
                        return null;
                    }
                    return object.meta.licence || object.meta.created || object.meta.modified || (object.meta.description && object.meta.description !== '');
                }
            };
        }
    ]);
})();