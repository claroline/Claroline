/**
 * UserPaper Service
 * Manages Paper of the current User
 * @param {Object}          $http
 * @param {Object}          $q
 * @param {PaperService}    PaperService
 * @param {ExerciseService} ExerciseService
 * @constructor
 */
var UserPaperService = function UserPaperService($http, $q, PaperService, ExerciseService) {
    this.$http           = $http;
    this.$q              = $q;
    this.PaperService    = PaperService;
    this.ExerciseService = ExerciseService;
};

// Set up dependency injection
UserPaperService.$inject = [ '$http', '$q', 'PaperService', 'ExerciseService' ];

/**
 * Current paper of the User
 * @type {Object}
 */
UserPaperService.prototype.paper = {};

/**
 * Number of papers already done by the User
 * @type {number}
 */
UserPaperService.prototype.nbPapers = 0;

/**
 * Get Paper
 * @returns {Object}
 */
UserPaperService.prototype.getPaper = function getPaper() {
    return this.paper;
};

/**
 * Set Paper
 * @param   {Object} paper
 * @returns {UserPaperService}
 */
UserPaperService.prototype.setPaper = function setPaper(paper) {
    this.paper = paper;

    return this;
};

/**
 * Get number of Papers
 * @returns {number}
 */
UserPaperService.prototype.getNbPapers = function getNbPapers() {
    return this.nbPapers;
};

/**
 * Set number of Papers
 * @param {number} count
 * @returns {UserPaperService}
 */
UserPaperService.prototype.setNbPapers = function setNbPapers(count) {
    this.nbPapers = count ? parseInt(count) : 0;

    return this;
};

/**
 * Order the Questions of a Step
 * @param   {Object} step
 * @returns {Array} The ordered list of Questions
 */
UserPaperService.prototype.orderQuestions = function orderQuestions(step) {
    return this.PaperService.orderStepQuestions(this.paper, step);
};

/**
 * Get Paper for a Question
 * @param {Object} question
 */
UserPaperService.prototype.getQuestionPaper = function getQuestionPaper(question) {
    return this.PaperService.getQuestionPaper(this.paper, question);
};

/**
 * Start the Exercise
 * @param   {Object} exercise
 * @returns {promise}
 */
UserPaperService.prototype.start = function start(exercise) {
    var deferred = this.$q.defer();

    this.$http.post(
        Routing.generate('exercise_new_attempt', { id: exercise.id })
    ).success(function(response){
        // TODO : display message

        if (response && response.paper) {
            this.paper = response.paper;

            deferred.resolve(response.paper);
        }
    }.bind(this)).error(function(data, status){
        // TODO : display message

        deferred.reject([]);
        var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService get exercise error';
        var code = data && data.error && data.error.code ? data.error.code : 403;
        /*var url = Routing.generate('ujm_sequence_error', { message: msg, code: code });*/
        /*$window.location = url;*/
    });

    return deferred.promise;
};

/**
 * End the Exercise
 * @returns {promise}
 */
UserPaperService.prototype.end = function end() {
    var deferred = this.$q.defer();

    this.$http
        .put(
            Routing.generate('exercise_finish_paper', { id: this.paper.id })
        )
        // Success callback
        .success(function (response) {
            // Update the number of finished papers
            this.nbPapers++;

            // TODO : display message

            deferred.resolve(this.paper);
        }.bind(this))
        // Error callback
        .error(function (data, status) {
            // TODO : display message

            deferred.reject([]);

            var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService end sequence error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            /*var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});*/
            /*$window.location = url;*/
        });

    return deferred.promise;
};

/**
 * Use an hint
 * @returns {promise}
 */
UserPaperService.prototype.useHint = function useHint(question, hint) {
    var deferred = this.$q.defer();
    this.$http
        .get(
            Routing.generate('exercise_hint', { paperId: this.paper.id, hintId: hint.id })
        )
        .success(function (response) {
            // Update question Paper with used hint
            var questionPaper = this.getQuestionPaper(question);

            questionPaper.hints.push({
                id     : hint.id,
                penalty: hint.penalty,
                value  : response
            });

            deferred.resolve(response);
        }.bind(this))
        .error(function (data, status) {
            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'QuestionService get hint error';
            var code = data && data.error && data.error.code ? data.error.code : 400;
            /*var url = Routing.generate('ujm_sequence_error', {message:msg, code:code});*/
            /*$window.location = url;*/
        });

    return deferred.promise;
};

/**
 * Submit Step answers to the server
 * @param {Object} step
 */
UserPaperService.prototype.submitStep = function submitStep(step) {
    var deferred = this.$q.defer();

    // Get answers for each Question of the Step
    var noAnswer = true;
    var stepAnswers = {};
    if (step && step.items) {
        for (var i = 0; i < step.items.length; i++) {
            var item      = step.items[i];
            var itemPaper = this.getQuestionPaper(item);

            if (itemPaper && itemPaper.answer) {
                stepAnswers[item.id] = itemPaper.answer;

                // At least one answer found
                noAnswer = false;
            }
        }
    }

    if (!noAnswer) {
        // There are answers to post
        this.$http
            .put(
                Routing.generate('exercise_submit_step', { paperId: this.paper.id, stepId: step.id }),
                { data: stepAnswers }
            )

            // Success callback
            .success(function onSuccess(response) {
                if (response) {
                    for (var i = 0; i < response.length; i++) {
                        if (response[i]) {
                            var item = null;

                            // Get item in Step
                            for (var j = 0; j < step.items.length; j++) {
                                if (response[i].question.id === step.items[j].id) {
                                    item = step.items[j];
                                    break; // Stop searching
                                }
                            }

                            if (item) {
                                // Update question with solutions and feedback
                                item.solutions = response[i].question.solutions ? response[i].question.solutions : [];
                                item.feedback  = response[i].question.feedback  ? response[i].question.feedback  : null;

                                // Update paper with Score
                                var paper = this.getQuestionPaper(item);
                                paper.score = response[i].score;
                            }
                        }
                    }
                }

                deferred.resolve(response);
            }.bind(this))

            // Error callback
            .error(function (data, status) {
                // TODO : display message

                deferred.reject([]);
                var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService submit answer error';
                var code = data && data.error && data.error.code ? data.error.code : 403;
                /*var url = Routing.generate('ujm_sequence_error', { message: msg, code: code });*/
                //$window.location = url;
            });
    } else {
        deferred.resolve(null);
    }

    return deferred.promise;
};

/**
 * Check if the User is allowed to compose (max attempts of the Exercise is not reached)
 * @returns {boolean}
 */
UserPaperService.prototype.isAllowedToCompose = function isAllowedToCompose() {
    var allowed = true;

    var exercise = this.ExerciseService.getExercise();
    if (exercise.meta.maxAttempts && this.nbPapers >= exercise.meta.maxAttempts) {
        // Max attempts reached => user can not do the exercise
        allowed = false;
    }

    return allowed;
};

// Register service into AngularJS
angular
    .module('Paper')
    .service('UserPaperService', UserPaperService);