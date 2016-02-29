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
 * Is the Current Exercise already published ?
 * @type {boolean}
 */
ExerciseService.prototype.editEnabled = false;

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
 * Start the current Exercise
 * @returns {promise}
 */
ExerciseService.prototype.start = function start() {
    // Backup CODE
    var deferred = this.$q.defer();

    this.$http.post(
            Routing.generate('exercise_new_attempt', {id: id})
        ).success(function(response){
            deferred.resolve(response);
        }).error(function(data, status){
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
    var deferred = this.$q.defer();

    this.$http
        .put(
            Routing.generate('exercise_finish_paper', {id: studentPaper.id})
        )
        // Success callback
        .success(function (response) {
            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            deferred.reject([]);

            var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService end sequence error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
            /*$window.location = url;*/
        });

    return deferred.promise;
};

/**
 * Publish the current Exercise
 * @returns {ExerciseService}
 */
ExerciseService.prototype.publish = function publish() {
    return this;
};

/**
 * Unpublish the current Exercise
 * @returns {ExerciseService}
 */
ExerciseService.prototype.unpublish = function unpublish() {
    return this;
};

/**
 * Save the answer given to a question
 * @param {number} paperId
 * @param {object} answer
 * @returns promise
 */
ExerciseService.prototype.submitAnswer = function submitAnswer(paperId, studentData) {
    var deferred = this.$q.defer();

    this.$http
        .put(
            Routing.generate('exercise_submit_answer', {paperId: paperId, questionId: studentData.question.id}), {data: studentData.answers}
        )
        // Success callback
        .success(function (response) {
            deferred.resolve(response);
        })
        // Error callback
        .error(function (data, status) {
            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'ExerciseService submit answer error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
            //$window.location = url;
        });

    return deferred.promise;
}

// Register service into AngularJS
angular
    .module('Exercise')
    .service('ExerciseService', ExerciseService);