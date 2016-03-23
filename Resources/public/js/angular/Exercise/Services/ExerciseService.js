/**
 * Exercise Service
 */
var ExerciseService = function ExerciseService($http, $q) {
    this.$http = $http;
    this.$q    = $q;
};

// Set up dependency injection
ExerciseService.$inject = [ '$http', '$q' ];

/**
 * Current Exercise
 * @type {Object}
 */
ExerciseService.prototype.exercise = null;

/**
 * Is the current User can edit the Exercise ?
 * @type {boolean}
 */
ExerciseService.prototype.editEnabled = false;

/**
 * Is the current User can do the Exercise ?
 * @type {boolean}
 */
ExerciseService.prototype.composeEnabled = false;

/**
 * Get the current Exercise
 * @returns {Object}
 */
ExerciseService.prototype.getExercise = function getExercise() {
    return this.exercise;
};

/**
 * Set the current Exercise
 * @param   {Object} exercise
 * @returns {ExerciseService}
 */
ExerciseService.prototype.setExercise = function setExercise(exercise) {
    this.exercise = exercise;

    return this;
};

/**
 * Get steps of an Exercise
 * @returns {Array}
 */
ExerciseService.prototype.getSteps = function getSteps() {
    return (this.exercise && this.exercise.steps) ? this.exercise.steps : [];
};

/**
 * Is edit enabled ?
 * @returns {boolean}
 */
ExerciseService.prototype.isEditEnabled = function isEditEnabled() {
    return this.editEnabled;
};

/**
 * Set edit enabled
 * @param   {boolean} editEnabled
 * @returns {ExerciseService}
 */
ExerciseService.prototype.setEditEnabled = function setEditEnabled(editEnabled) {
    this.editEnabled = editEnabled;

    return this;
};

/**
 * Is compose enabled ?
 * @returns {boolean}
 */
ExerciseService.prototype.isComposeEnabled = function isComposeEnabled() {
    return this.composeEnabled;
};

/**
 * Set compose enabled
 * @param   {boolean} composeEnabled
 * @returns {ExerciseService}
 */
ExerciseService.prototype.setComposeEnabled = function setComposeEnabled(composeEnabled) {
    this.composeEnabled = composeEnabled;

    return this;
};

/**
 * Save modifications of the metadata of the Exercise
 * @param exercise
 * @returns {Promise}
 */
ExerciseService.prototype.save = function save(exercise) {
    var deferred = this.$q.defer();

    this.$http
        .put(
            Routing.generate('ujm_exercise_update_meta', { id: exercise.id }),
            exercise.meta
        )
        .success(function onSuccess(response) {
            // TODO : display message

            deferred.resolve(response);
        })
        .error(function onError(response, status) {
            // TODO : display message

            deferred.reject(response);
        });

    return deferred.promise;
};

/**
 * Start the current Exercise
 * @returns {promise}
 */
ExerciseService.prototype.start = function start() {
    var deferred = this.$q.defer();

    this.$http.post(
            Routing.generate('exercise_new_attempt', { id: this.exercise.id })
        ).success(function(response){
            // TODO : display message

            if (response && response.paper) {
                deferred.resolve(response.paper);
            }
        }).error(function(data, status){
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
 * End the current Exercise
 * @param   {Object} studentPaper
 * @returns {promise}
 */
ExerciseService.prototype.end = function end(studentPaper) {
    // Backup CODE
    var deferred = this.$q.defer();

    this.$http
        .put(
            Routing.generate('exercise_finish_paper', { id: studentPaper.id })
        )
        // Success callback
        .success(function (response) {
            // TODO : display message

            deferred.resolve(response);
        })
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
 * Add a new Step to the Exercise
 */
ExerciseService.prototype.addStep = function addStep() {
    if (!this.exercise.steps) {
        this.exercise.steps = [];
    }

    // Initialize a new Step
    var step = {
        id: null,
        items: []
    };

    // Add to the steps list
    this.exercise.steps.push(step);

    // Send step to the server
    var deferred = this.$q.defer();
    this.$http
        .post(
            Routing.generate('ujm_exercise_step_add', { id: this.exercise.id }),
            step
        )
        // Success callback
        .success(function (response) {
            // Get the information of the Step
            step.id = response.id;

            // TODO : display success message

            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            // Remove step
            var pos = this.exercise.steps.indexOf(step);
            if (-1 !== pos) {
                this.exercise.steps.splice(pos, 1);
            }

            // TODO : display error message

            deferred.reject({});
        }.bind(this));

    return deferred.promise;
};

ExerciseService.prototype.removeStep = function removeStep(step) {
    // Store a copy of the item if something goes wrong
    var stepBack = angular.copy(step, {});

    // Remove item from Step
    var pos = this.exercise.steps.indexOf(step);
    if (-1 !== pos) {
        this.exercise.steps.splice(pos, 1);
    }

    var deferred = this.$q.defer();
    this.$http
        .delete(
            Routing.generate('ujm_exercise_step_delete', { id: this.exercise.id, sid: step.id })
        )
        // Success callback
        .success(function (response) {
            // TODO : display success message

            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            // Restore item
            // TODO : push step at the correct position
            this.exercise.steps.push(stepBack);

            // TODO : display error message

            deferred.reject({});
        }.bind(this));

    return deferred.promise;
};

ExerciseService.prototype.removeItem = function removeItem(step, item) {
    // Store a copy of the item if something goes wrong
    var itemBack = angular.copy(item, {});

    // Remove item from Step
    var pos = step.items.indexOf(item);
    if (-1 !== pos) {
        step.items.splice(pos, 1);
    }

    var deferred = this.$q.defer();
    this.$http
        .delete(
            Routing.generate('ujm_exercise_question_delete', { id: this.exercise.id, qid: item.id })
        )
        // Success callback
        .success(function (response) {
            // TODO : display success message

            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            // Restore item
            step.items.push(itemBack);

            // TODO : display error message

            deferred.reject({});
        }.bind(this));

    return deferred.promise;
};

/**
 * Publish the current Exercise
 * @returns {ExerciseService}
 */
ExerciseService.prototype.publish = function publish() {
    var deferred = this.$q.defer();

    // We anticipate the success of the publishing (that's just a boolean change on boolean flag)
    var publishedOnceBackup = this.exercise.meta.publishedOnce;

    this.exercise.meta.published     = true;
    this.exercise.meta.publishedOnce = true;

    this.$http
        .post(
            Routing.generate('ujm_exercise_publish', { id: this.exercise.id })
        )
        // Success callback
        .success(function (response) {
            // TODO : display success message

            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            // Remove published flags
            this.exercise.meta.published     = false;
            this.exercise.meta.publishedOnce = publishedOnceBackup;

            // TODO : display error message

            deferred.reject({});
        }.bind(this));

    return deferred.promise;
};

/**
 * Unpublish the current Exercise
 * @returns {ExerciseService}
 */
ExerciseService.prototype.unpublish = function unpublish() {
    var deferred = this.$q.defer();

    // We anticipate the success of the publishing (that's just a boolean change on boolean flag)
    this.exercise.meta.published = false;

    this.$http
        .post(
            Routing.generate('ujm_exercise_unpublish', { id: this.exercise.id })
        )
        // Success callback
        .success(function (response) {
            // TODO : display success message

            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            // Remove published flags
            this.exercise.meta.published = true;

            // TODO : display error message

            deferred.reject({});
        }.bind(this));

    return deferred.promise;
};

/**
 * Save the answer given to a question
 * @param {number} paperId
 * @param {object} studentData
 * @returns promise
 */
ExerciseService.prototype.submitAnswer = function submitAnswer(paperId, studentData) {
    // Backup CODE
    var deferred = this.$q.defer();

    this.$http
        .put(
            Routing.generate('exercise_submit_answer', {paperId: paperId, questionId: studentData.question.id}), {data: studentData.answers}
        )
        // Success callback
        .success(function (response) {
            // TODO : display message

            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            // TODO : display message

            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService submit answer error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
            //$window.location = url;
        });

    return deferred.promise;
};

// Register service into AngularJS
angular
    .module('Exercise')
    .service('ExerciseService', ExerciseService);