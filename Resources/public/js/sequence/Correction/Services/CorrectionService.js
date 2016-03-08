/**
 * CorrectionService
 */
(function () {
    'use strict';

    angular.module('Correction').factory('CorrectionService', [
        '$window',
        '$http',
        '$filter',
        '$q',
        function CorrectionService($window, $http, $filter, $q) {

            return {
                getMatchQuestionScore: function (question, paper) {
                    var availableScore = 0.0;
                    var studentScore = 0.0;
                    var result = '';
                    // do not add same association score twice
                    var currentLabelId = '';
                    var $tabLabelGraduate = [];
                    for(var i = 0; i < question.solutions.length; i++){

                        for(var u = 0; u < $tabLabelGraduate.length; u++){

                            if($tabLabelGraduate[u] !== question.solutions[i].secondId){

                                if(currentLabelId !== question.solutions[i].secondId){

                                    availableScore += question.solutions[i].score ? question.solutions[i].score : 0;
                                }
                                currentLabelId = question.solutions[i].secondId;
                            }
                        }
                        $tabLabelGraduate.push(question.solutions[i].secondId);
                    }
                    for (var j = 0; j < paper.questions.length; j++) {
                        if (paper.questions[j].id === question.id.toString()) {
                            studentScore = paper.questions[j].score;
                        }
                    }
                    result = studentScore.toString() + '/' + availableScore.toString();
                    return result;

                },
                getChoiceQuestionScore: function (question, paper) {
                    var availableScore = 0.0;
                    var studentScore = 0.0;
                    var result = '';
                    for (var i = 0; i < question.solutions.length; i++) {
                        availableScore += question.solutions[i].score ? question.solutions[i].score : 0;
                    }
                    for (var j = 0; j < paper.questions.length; j++) {
                        if (paper.questions[j].id === question.id.toString()) {
                            studentScore = paper.questions[j].score;
                        }
                    }
                    result = studentScore.toString() + '/' + availableScore.toString();
                    return result;
                },
                getClozeQuestionScore: function (question, paper) {
                    var availableScore = 0.0;
                    var studentScore = 0.0;
                    var result = '';
                    for (var i = 0; i < question.holes.length; i++) {
                        var higher_score = 0;
                        for (var j = 0; j < question.holes[i].wordResponses.length; j++) {
                            if (question.holes[i].wordResponses[j].score > higher_score) {
                                higher_score = question.holes[i].wordResponses[j].score;
                            }
                        }
                        availableScore += higher_score;
                    }
                    for (var j = 0; j < paper.questions.length; j++) {
                        if (paper.questions[j].id === question.id.toString()) {
                            studentScore = paper.questions[j].score;
                        }
                    }
                    result = studentScore.toString() + '/' + availableScore.toString();
                    return result;
                },
                getShortQuestionScore: function (question, paper) {
                    if (question.typeOpen === "long") {
                        for (var j = 0; j < paper.questions.length; j++) {
                            if (paper.questions[j].id === question.id.toString()) {
                                if (paper.questions[j].score !== -1) {
                                    result = paper.questions[j].score.toString() + '/' + question.scoreMaxLongResp.toString();
                                    return result;
                                } else {
                                    return Translator.trans('need_correction', {}, 'ujm_sequence');
                                }
                            }
                        }
                    } else {
                        var availableScore = 0.0;
                        var studentScore = 0.0;
                        var result = '';
                        for (var i = 0; i < question.solutions.length; i++) {
                            availableScore += question.solutions[i].score ? question.solutions[i].score : 0;
                        }
                        for (var j = 0; j < paper.questions.length; j++) {
                            if (paper.questions[j].id === question.id.toString()) {
                                studentScore = paper.questions[j].score;
                            }
                        }
                        result = studentScore.toString() + '/' + availableScore.toString();
                        return result;
                    }
                },
                getGraphicQuestionScore: function (question, paper) {
                    var availableScore = 0.0;
                    var studentScore = 0.0;
                    // get coords for each solution
                    for (var i = 0; i < question.solutions.length; i++) {
                        var solution = question.solutions[i];
                        var coords = solution.value.split(',');
                        var expected = {
                            x: parseFloat(coords[0]),
                            y: parseFloat(coords[1]),
                            size: solution.size,
                            score: solution.score
                        };

                        availableScore += solution.score;
                        // get the corresponding answer
                        for (var j = 0; j < paper.questions.length; j++) {
                            if (paper.questions[j].id === question.id.toString()) {
                                for (var k = 0; k < paper.questions[j].answer.length; k++) {
                                    var answers = paper.questions[j].answer[k].split('-');
                                    if (answers[0].trim() !== 'a' && answers[1].trim() !== 'a') {
                                        var x = parseFloat(answers[0].trim());
                                        var y = parseFloat(answers[1].trim());
                                        if (x <= (expected.x + expected.size) && x >= expected.x && y <= (expected.y + expected.size) && y >= expected.y) {
                                            studentScore += expected.score;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    return studentScore.toString() + '/' + availableScore.toString();
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
                                deferred.reject([]);
                                var msg = data && data.error && data.error.message ? data.error.message : 'Correction get one error';
                                var code = data && data.error && data.error.code ? data.error.code : 403;
                                var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                /**
                 * Save a long answer's score
                 * @param {type} exoId
                 * @param {type} paperId
                 * @param {type} score
                 * @returns {$q@call;defer.promise}
                 */
                saveScore: function (questionId, paperId, score) {
                    var deferred = $q.defer();
                    $http
                            .put(
                                    //finish_paper
                                    Routing.generate('exercise_save_open_score', {questionId: questionId, paperId: paperId, score: score})
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
                }
            };
        }
    ]);
})();