/**
 * Papers service
 * @param {Object}           $http
 * @param {Object}           $q
 * @param {ExerciseService}  ExerciseService
 * @param {StepService}      StepService
 * @param {QuestionService}  QuestionService
 * @constructor
 */
var PaperService = function PaperService($http, $q, ExerciseService, StepService, QuestionService) {
    this.$http           = $http;
    this.$q              = $q;
    this.ExerciseService = ExerciseService;
    this.StepService     = StepService;
    this.QuestionService = QuestionService;
};

// Set up dependency injection
PaperService.$inject = [ '$http', '$q', 'ExerciseService', 'StepService', 'QuestionService' ];

/**
 * Number of papers already done for the current Exercise
 * @type {number}
 */
PaperService.prototype.nbPapers = 0;

/**
 * Get number of Papers
 * @returns {number}
 */
PaperService.prototype.getNbPapers = function getNbPapers() {
    return this.nbPapers;
};

/**
 * Set number of Papers
 * @param {number} count
 * @returns {PaperService}
 */
PaperService.prototype.setNbPapers = function setNbPapers(count) {
    this.nbPapers = count ? parseInt(count) : 0;

    return this;
};

/**
 * Get one paper details
 * @param   {String} id
 * @returns {Promise}
 */
PaperService.prototype.get = function get(id) {
    var exercise = this.ExerciseService.getExercise();

    var deferred = this.$q.defer();
    this.$http
        .get(Routing.generate('exercise_paper', { exerciseId: exercise.id, paperId: id }))
        .success(function (response) {
            deferred.resolve(response);
        })
        .error(function (data, status) {
            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'Correction get one error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            /*var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});*/
            /*$window.location = url;*/
        });

    return deferred.promise;
};

/**
 * Get all papers for an Exercise
 * @returns {Promise}
 */
PaperService.prototype.getAll = function getAll() {
    var exercise = this.ExerciseService.getExercise();

    var deferred = this.$q.defer();
    this.$http
        .get(Routing.generate('exercise_papers', { id: exercise.id }))
        .success(function (response) {
            this.setNbPapers(response.length);

            deferred.resolve(response);
        }.bind(this))
        .error(function (data, status) {
            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'Papers get all error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            /*var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});*/

            /*$window.location = url;*/
        });

    return deferred.promise;
};

/**
 * Get Paper for a Question
 * @param {Object} paper
 * @param {Object} question
 */
PaperService.prototype.getQuestionPaper = function getQuestionPaper(paper, question) {
    var questionPaper = {};

    for (var i = 0; i < paper.questions.length; i++) {
        if (paper.questions[i].id == question.id) {
            // Question paper found
            questionPaper = paper.questions[i];

            // Initialize answers property
            if (!questionPaper.answer) {
                questionPaper.answer = this.QuestionService.getTypeService(question.type).initAnswer();
            }

            // Initialize hints property
            if (!questionPaper.hints) {
                questionPaper.hints  = [];
            }

            if (typeof questionPaper.score === 'undefined' || null === questionPaper.score) {
                questionPaper.score = 0;
            }

            break; // Stop searching
        }
    }

    if (0 === Object.keys(questionPaper).length) {
        // There is no Paper for the current Question => initialize Object properties
        questionPaper.id     = question.id;
        questionPaper.answer = this.QuestionService.getTypeService(question.type).initAnswer();
        questionPaper.hints  = [];

        // Add Question to the Paper
        paper.questions.push(questionPaper);
    }

    return questionPaper;
};

/**
 * Delete all papers of an Exercise
 */
PaperService.prototype.deleteAll = function deleteAll(papers) {
    var exercise = this.ExerciseService.getExercise();

    var deferred = this.$q.defer();
    this.$http
        .delete(Routing.generate('ujm_exercise_delete_papers', { id: exercise.id }))
        .success(function (response) {
            papers.splice(0, papers.length); // Empty the Papers list

            this.setNbPapers(0);

            deferred.resolve(response);
        }.bind(this))
        .error(function (data, status) {
            deferred.reject([]);
        });

    return deferred.promise;
};

/**
 * Delete a Paper
 * @param {Object} paper
 */
PaperService.prototype.delete = function deletePaper(paper) {

};

/**
 * Check whether a paper need manual correction
 * @param {Object} paper
 * @returns {Boolean}
 */
PaperService.prototype.needManualCorrection = function needManualCorrection(paper) {
    var needed = false;
    if (paper.questions && 0 !== paper.questions.length) {
        for(var i = 0; i < paper.questions.length; i++){
            if (-1 === paper.questions[i].score) {
                // The question has not been marked
                needed = true;
                break; // Stop searching
            }
        }
    }

    return needed;
};

/**
 * Calculate the score of the Paper (/20)
 * @param   {Object} paper
 * @returns {number}
 */
PaperService.prototype.getPaperScore = function getPaperScore(paper) {
    var score = 0.0; // final score
    var scoreTotal = this.ExerciseService.getScoreTotal();
    var userScore = paper.scoreTotal;
    if (userScore) {
        score = userScore * 20 / scoreTotal;
        if (userScore > 0) {
            score = Math.round(score / 0.5) * 0.5;
        } else {
            score = 0;
        }
    }

    return score;
};

/**
 * Order the Questions of a Step
 * @param   {Object} paper
 * @param   {Array}  questions
 * @returns {Array}
 */
PaperService.prototype.orderQuestions = function orderQuestions(paper, questions) {
    var ordered = [];

    if (paper && paper.order) {
        for (var i = 0; i < paper.order.length; i++) {
            var stepOrder = paper.order[i];
            for (var j = 0; j < stepOrder.items.length; j++) {
                var item = stepOrder.items[j];
                var question = this.QuestionService.getQuestion(questions, item);
                if (question) {
                    ordered.push(question);
                }
            }
        }
    }

    return ordered;
};

/**
 * Order the Questions of a Step
 * @param   {Object} paper
 * @param   {Object} step
 * @returns {Array} The ordered list of Questions
 */
PaperService.prototype.orderStepQuestions = function orderStepQuestions(paper, step) {
    var ordered = [];
    if (step.items && 0 !== step.items.length) {
        // Get order for the current Step
        var itemsOrder = null;
        if (paper && paper.order) {
            for (var i = 0; i < paper.order.length; i++) {
                if (step.id === paper.order[i].id) {
                    // Order for the current step found
                    itemsOrder = paper.order[i].items;
                    break; // Stop searching
                }
            }
        }

        if (itemsOrder) {
            for (var i = 0; i < itemsOrder.length; i++) {
                var question = this.StepService.getQuestion(step, itemsOrder[i]);
                if (question) {
                    ordered.push(question);
                }
            }
        } else {
            ordered = step.items;
        }
    }

    return ordered;
};

// Register service into AngularJS
angular
    .module('Paper')
    .service('PaperService', PaperService);