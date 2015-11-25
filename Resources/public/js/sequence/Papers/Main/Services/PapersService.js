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

            this.displayRetryExerciseLink = false;

            return {
                /**
                 * get all papers for an exercise
                 * @param {string} id
                 * @returns {$q@call;defer.promise}
                 */
                getAll: function (id, user) {
                    var deferred = $q.defer();
                    var url = '';
                    if (user.admin) {
                        url = Routing.generate('exercise_papers_admin', {id: id})
                    } else {
                        url = Routing.generate('exercise_papers', {id: id})
                    }
                    $http
                            .get(
                                    url
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                console.log('PapersService, getAll method error');
                                console.log(data);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                /**
                 * Get one paper details
                 * @param {type} exoId
                 * @param {type} paperId
                 * @returns {$q@call;defer.promise}
                 */
                getOne: function (exoId, paperId) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('exercise_paper', {exerciseId: exoId, paperId: paperId})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                console.log('PapersService, getOne method error');
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                /**
                 * Get paper's exercise info
                 * @param {type} id
                 * @returns {$q@call;defer.promise}
                 */
                getSequence: function (id) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('exercise_get_minimal', {id: id})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                /**
                 * Calculate the global score for a paper
                 * @param {type} paper
                 * @param {type} questions
                 * @returns {Number}
                 */
                getPaperScore: function (paper, questions) {
                    var score = 0.0; // final score
                    var totalPoints = this.getExerciseTotalScore(questions);
                    var studentPoints = 0.0; // good answers

                    for (var i = 0; i < paper.questions.length; i++) {
                        // paper question item contains student answer, used hints
                        var currentPaperQuestion = paper.questions[i];

                        // for each given answer
                        if (currentPaperQuestion.answer) {
                            for (var j = 0; j < currentPaperQuestion.answer.length; j++) {
                                var id = currentPaperQuestion.answer[j];
                                studentPoints += this.getAnswerScore(id, questions);
                            }
                            // for each used hints
                            for (var k = 0; k < currentPaperQuestion.hints.length; k++) {
                                // find hint penalty in questions collection
                                var penalty = this.getHintPenalty(currentPaperQuestion.hints[k], questions);
                                // remove penalty value from student points
                                studentPoints -= penalty;
                            }
                        }
                    }
                    score = studentPoints * 20 / totalPoints;
                    return score > 0 ? (Math.round(score / 0.5) * 0.5) : 0;
                },
                /**
                 * get available score in the exercise
                 * @param {type} questions
                 * @returns {Number}
                 */
                getExerciseTotalScore: function (questions) {
                    var nbQuestions = questions.length;
                    var score = 0.0;
                    for (var i = 0; i < nbQuestions; i++) {
                        var currentQuestion = questions[i];
                        // update exercise total points
                        for (var j = 0; j < currentQuestion.solutions.length; j++) {
                            // update total points for the sequence
                            score += currentQuestion.solutions[j].score;
                        }
                    }
                    return score;
                },
                getHintPenalty: function (searched, questions) {
                    var nbQuestions = questions.length;
                    var penalty = 0.0;
                    for (var i = 0; i < nbQuestions; i++) {
                        var currentQuestion = questions[i];
                        if (currentQuestion.hints) {
                            // update exercise total points
                            for (var j = 0; j < currentQuestion.hints.length; j++) {
                                if (currentQuestion.hints[j].id === searched) {
                                    penalty = currentQuestion.hints[j].penalty;
                                }
                            }
                        }
                    }
                    return penalty;
                },
                getAnswerScore: function (searched, questions) {
                    var nbQuestions = questions.length;
                    var score = 0.0;
                    for (var i = 0; i < nbQuestions; i++) {
                        var currentQuestion = questions[i];
                        // update exercise total points
                        for (var j = 0; j < currentQuestion.solutions.length; j++) {
                            if (currentQuestion.solutions[j].id === searched) {
                                score = currentQuestion.solutions[j].score;
                            }
                        }
                    }
                    return score;
                },
                getConnectedUser: function (id) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('paper_get_connected_user', {id: id})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                countFinishedPaper:function(id){
                    var deferred = $q.defer();
                    $http
                            .get(
                                Routing.generate('exercise_papers_count', {id:id})
                            )
                            .success(function (response){
                                deferred.resolve(response);
                            })
                            .error(function(data, status){
                                 deferred.reject([]);    
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });
                    return deferred.promise;
                },
                /**
                 * Checks if current user can replay the exercise
                 * Only used if current user is not admin and exercise maxAttempts property is != 0
                 * @param {number} max
                 * @param {Object} paper list
                 * @returns {Boolean}
                 */
                userCanReplayExercise: function (max, papers) {
                    var lastPaperNumber = this.getPaperMaxNumber(papers);
                    return lastPaperNumber < max;
                },
                getPaperMaxNumber: function (papers) {
                    var max = 0;
                    for (var i = 0; i < papers.length; i++) {
                        if (max < papers[i].number && papers[i].end !== '' && papers[i].end !== undefined && !papers[i].interrupted) {
                            max = papers[i].number;
                        }
                    }
                    return max;
                }
            };
        }
    ]);
})();