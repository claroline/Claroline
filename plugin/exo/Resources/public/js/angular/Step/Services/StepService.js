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

// Register service into AngularJS
angular
    .module('Step')
    .service('StepService', StepService);