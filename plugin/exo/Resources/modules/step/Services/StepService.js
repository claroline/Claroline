/**
 * Step Service
 * @param {Object}          $http
 * @param {Object}          $q
 * @param {ExerciseService} ExerciseService
 * @param {QuestionService} QuestionService
 * @constructor
 */
var StepService = function StepService($http, $q, ExerciseService, QuestionService) {
    this.$http = $http;
    this.$q = $q;
    this.ExerciseService = ExerciseService;
    this.QuestionService = QuestionService;
};

// Set up dependency injection
StepService.$inject = [ '$http', '$q', 'ExerciseService', 'QuestionService' ];

/**
 * Reorder the Steps of the current Exercise.
 */
StepService.prototype.reorderItems = function reorderSteps(step) {
    var exercise = this.ExerciseService.getExercise();

    // Only send the IDs of the steps
    var order = step.items.map(function getIds(item) {
        return item.id;
    });

    var deferred = this.$q.defer();
    this.$http
        .put(
            Routing.generate('exercise_question_reorder', { exerciseId: exercise.id, id: step.id }),
            order
        )
        .success(function onSuccess(response) {
            deferred.resolve(response);
        })
        .error(function onError(data, status) {
            deferred.reject([]);
        });

    return deferred.promise;
};

/**
 * Get a Step question by its ID
 * @param   {Object} step
 * @param   {String} questionId
 * @returns {Object|null}
 */
StepService.prototype.getQuestion = function getQuestion(step, questionId) {
    var question = null;

    if (step && step.items && 0 !== step.items.length) {
        question = this.QuestionService.getQuestion(step.items, questionId);
    }

    return question;
};

/**
 * Save modifications of the metadata of the Step
 * @param   {Object} step
 * @param   {Object} meta
 * @returns {Promise}
 */
StepService.prototype.save = function save(step, meta) {
    var exercise = this.ExerciseService.getExercise();
    var deferred = this.$q.defer();
    this.$http
        .put(
            Routing.generate('exercise_step_update_meta', { exerciseId: exercise.id, id: step.id }),
            meta
        )
        .success(function onSuccess(response) {
            // Inject updated data into the Exercise
            angular.merge(step.meta, response.meta);

            deferred.resolve(response);
        })
        .error(function onError(response, status) {
            // TODO : display message

            deferred.reject(response);
        });

    return deferred.promise;
};

// Register service into AngularJS
angular
    .module('Step')
    .service('StepService', StepService);