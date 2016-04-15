/**
 * UserPaper Service
 * Manages Paper of the current User
 * @constructor
 */
var UserPaperService = function UserPaperService($http, $q) {
    this.$http = $http;
    this.$q    = $q;
};

// Set up dependency injection
UserPaperService.$inject = [ '$http', '$q' ];

/**
 * Current paper of the User
 * @type {Object}
 */
UserPaperService.prototype.paper = {};

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
 * Get Paper for a Question
 * @param {Object} question
 *
 * @todo Do not send empty answers and cancel submission if all step answers are empty
 */
UserPaperService.prototype.getQuestionPaper = function getQuestionPaper(question) {
    var questionPaper = {};

    for (var i = 0; i < this.paper.questions.length; i++) {
        if (this.paper.questions[i].id == question.id) {
            // Question paper found
            questionPaper = this.paper.questions[i];

            // Initialize answers property
            if (!questionPaper.answer) {
                questionPaper.answer = null;
            }

            // Initialize hints property
            if (!questionPaper.hints) {
                questionPaper.hints  = [];
            }

            break; // Stop searching
        }
    }

    if (0 === Object.keys(questionPaper).length) {
        // There is no Paper for the current Question => initialize Object properties
        questionPaper.id     = question.id;
        questionPaper.answer = null;
        questionPaper.hints  = [];

        // Add Question to the Paper
        this.paper.questions.push(questionPaper);
    }

    return questionPaper;
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
        var url = Routing.generate('ujm_sequence_error', { message: msg, code: code });
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
            // TODO : display message

            deferred.resolve(this.paper);
        }.bind(this))
        // Error callback
        .error(function (data, status) {
            // TODO : display message

            deferred.reject([]);

            var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService end sequence error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
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
            var url = Routing.generate('ujm_sequence_error', {message:msg, code:code});
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
                var url = Routing.generate('ujm_sequence_error', { message: msg, code: code });
                //$window.location = url;
            });
    } else {
        deferred.resolve(null);
    }

    return deferred.promise;
};

// Register service into AngularJS
angular
    .module('Paper')
    .service('UserPaperService', UserPaperService);