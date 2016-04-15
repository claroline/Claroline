/**
 * CommonService
 */
angular.module('Common').factory('CommonService', [
    '$http',
    '$filter',
    '$q',
    function CommonService($http, $filter, $q) {
        this.paper = {};
        this.currentQuestion = {};
        this.currentAnswer = {};
        this.currentQuestionPaperData = {};

        return {
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

            // UTILS METHODS
            /**
             * get a sequence correction mode in a human readable word
             * @param {integer} mode
             * @returns {string} the humanized correction mode
             */
            getCorrectionMode: function (mode) {
                switch (mode) {
                    case "1":
                        return "test-end";
                        break;
                    case "2":
                        return "last-try";
                        break;
                    case "3":
                        return "after-date";
                        break;
                    case "4":
                        return "never";
                        break;
                    default:
                        return "never";
                }
            },

            /**
             * @param {object} object a javascript object with type property
             * @returns null or string
             */
            getObjectSimpleType: function (object) {
                var simpleType = null;
                switch (object.type) {
                    case 'text/html':
                        simpleType = 'html-text';
                        break;

                    case 'text/plain':
                        simpleType = 'simple-text';
                        break;

                    case 'application/pdf':
                        simpleType = 'web-pdf';
                        break;

                    case 'image/png':
                    case 'image/jpg':
                    case 'image/jpeg':
                        simpleType = 'web-image';
                        if (object.encoding && object.data) {
                            // Image is encoded
                            simpleType = 'encoded-image';
                        }
                        break;

                    default:
                        simpleType = null;
                        break;
                }

                return simpleType;
            }
        };
    }
]);



