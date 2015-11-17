/**
 * CommonService
 */
(function () {
    'use strict';
    angular.module('Common').factory('CommonService', [
        '$http',
        '$filter',
        '$q',
        function CommonService($http, $filter, $q) {

            this.sequence = {};
            this.paper = {};
            this.user;
            this.currentQuestion = {};
            this.currentAnswer = {};
            this.currentQuestionPaperData = {};

            return {
                /**
                 * Set the current sequence
                 * Used for sharing data between directives
                 * @param {type} sequence
                 * @returns {undefined}
                 */
                setSequence: function (sequence) {
                    this.sequence = sequence;
                    return this.sequence;
                },
                getSequence: function () {
                    return this.sequence;
                },
                getSequenceMeta: function () {
                    return this.sequence.meta;
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
                },
                // set / update the student data
                setStudentData: function (question, currentQuestionPaperData) {
                    this.currentQuestion = question;
                    // this will automatically update the paper object
                    if (currentQuestionPaperData) {
                        this.currentQuestionPaperData = currentQuestionPaperData;
                    }
                },
                getStudentData: function () {
                    return{
                        question: this.currentQuestion,
                        paper: this.paper,
                        answers: this.currentQuestionPaperData.answer
                    };
                },
                setPaper: function (paper) {
                    this.paper = paper;
                    return this.paper;
                },
                getPaper: function () {
                    return this.paper;
                },
                setUser: function (id) {
                    this.user = id;
                    return this.user;
                },
                getUser: function () {
                    return this.user;
                },
                /**
                 * return paper anwser(s) and used hints for the current question
                 * @param {type} id question id
                 * @returns {object}
                 */
                getCurrentQuestionPaperData: function (question) {
                    // search for an existing answer to the question in paper
                    for (var i = 0; i < this.paper.questions.length; i++) {
                        if (this.paper.questions[i].id === question.id.toString()) {
                            this.currentQuestionPaperData = this.paper.questions[i];
                            return this.currentQuestionPaperData;
                        }
                    }
                    // if no info found, initiate object
                    this.currentQuestionPaperData = {
                        id: question.id,
                        answer: [],
                        hints: []
                    };
                    this.paper.questions.push(this.currentQuestionPaperData);
                    return this.currentQuestionPaperData;
                },
                // UTILS METHODS
                /**
                 * get a sequence correction mode in a human readable word
                 * @param {integer} mode
                 * @returns {string} the humanized correction mode
                 */
                getCorrectionMode: function (mode) {
                    switch (mode) {
                        case 1:
                            return "test-end";
                            break;
                        case 2:
                            return "last-try";
                            break;
                        case 3:
                            return "after-date";
                            break;
                        case 4:
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
                    if (!object.type || object.type === undefined || object.type === 'undefined') {
                        return null;
                    } else {
                        var simpleType = null;
                        if (object.type === 'text/html') {
                            simpleType = 'html-text';
                        }
                        else if (object.type === 'text/plain') {
                            simpleType = 'simple-text';
                        }
                        else if (object.type === 'application/pdf' && object.url) {
                            simpleType = 'web-pdf';
                        }
                        else if ((object.type === 'image/png' || object.type === 'image/jpg' || object.type === 'image/jpeg') && object.url) {
                            simpleType = 'web-image';
                        }
                        else if ((object.type === 'image/png' || object.type === 'image/jpg' || object.type === 'image/jpeg') && object.encoding && object.data) {
                            simpleType = 'encoded-image';
                        }

                        return simpleType;
                    }
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
                generateUrl: function (witch, _id) {
                    switch (witch) {
                        case 'exercise-home':
                            return Routing.generate('ujm_exercise_open', {id: _id});
                            break;
                        case 'paper-list':
                            return Routing.generate('ujm_exercice_papers', {id: _id});
                            break;
                        case 'exercise-play':
                            return Routing.generate('ujm_exercise_play', {id: _id});
                            break;
                    }
                },
                getPaperScore: function (paper) {
                    var nbQuestions = paper.questions.length;

                    var score = 0.0; //final score
                    var totalPoints = 0.0;
                    var studentPoints = 0.0; // good answers

                    for (var i = 0; i < nbQuestions; i++) {
                        // paper question item contains student answer, used hints and solution 
                        var paperQuestion = paper.questions[i];

                        // update exercise total points
                        for (var j = 0; j < paperQuestion.choices.length; j++) {
                            // update total points for the sequence
                            totalPoints += paperQuestion.choices[j].score;
                        }

                        // for each given answer
                        for (var k = 0; k < paperQuestion.answer.length; k++) {
                            var id = paperQuestion.answer[k];
                            //  check if it is in solution
                            for (var l = 0; l < paperQuestion.solution.length; l++) {
                                if (paperQuestion.solution[l] === id) {
                                    // get the corresponding choice score... Only for choice question type...
                                    for (var m = 0; m < paperQuestion.choices.length; m++) {
                                        if (paperQuestion.choices[m].id === id) {
                                            // update student points
                                            studentPoints += paperQuestion.choices[m].score;
                                        }
                                    }
                                }
                            }
                        }
                        // for each used hints
                        for (var n = 0; n < paperQuestion.hints.length; n++) {
                            // remove penalty value from student points
                            studentPoints -= paperQuestion.hints[n].penalty;
                        }
                    }

                    //return (round($toBeAdjusted / 0.5) * 0.5);
                    score = studentPoints * 20 / totalPoints;
                    return score > 0 ? (Math.round(score / 0.5) * 0.5) : 0;
                },
                getPaperScore2: function (paper, questions) {


                    var score = 0.0; // final score
                    var totalPoints = this.getExerciseTotalScore(questions);
                    var studentPoints = 0.0; // good answers

                    for (var i = 0; i < paper.questions.length; i++) {
                        // paper question item contains student answer, used hints
                        var currentPaperQuestion = paper.questions[i];

                        // for each given answer
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
                    score = studentPoints * 20 / totalPoints;
                    return score > 0 ? (Math.round(score / 0.5) * 0.5) : 0;
                },
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
                    console.log('exercise total score ' + score);
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
                    console.log('penalty ' + penalty);
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
                    console.log('question score ' + score);
                    return score;
                }
            };
        }
    ]);
})();


