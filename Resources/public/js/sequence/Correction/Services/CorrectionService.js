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
                    for (var i = 0; i < question.solutions.length; i++) {
                        if (currentLabelId !== question.solutions[i].secondId) {
                            availableScore += question.solutions[i].score ? question.solutions[i].score : 0;
                        }
                        currentLabelId = question.solutions[i].secondId;
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
                        for (var j=0; j<question.holes[i].wordResponses.length; j++) {
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
                        return Translator.trans('need_correction', {}, 'ujm_sequence');
                    }
                    else {
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
                }
            };
        }
    ]);
})();