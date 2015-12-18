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
                /**
                 * NOT IMPLEMENTED YET
                 * @param {type} question
                 * @param {type} paper
                 * @returns {Number}
                 */
                getQuestionScore: function (question, paper) {
                    var solutions = question.solutions;
                    var hints = question.hints;
                    var score = 0.0;
                    for (var i = 0; i < paper.questions.length; i++) {
                        if (paper.questions[i].id === question.id.toString()) {

                        }
                    }

                    return score;

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