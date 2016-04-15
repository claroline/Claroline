/**
 * Papers service
 * @param {Object} $http
 * @param {Object} $q
 * @param {Object} ExerciseService
 * @constructor
 */
var PaperService = function PaperService($http, $q, ExerciseService) {
    this.$http           = $http;
    this.$q              = $q;
    this.ExerciseService = ExerciseService;
};

// Set up dependency injection
PaperService.$inject = [ '$http', '$q', 'ExerciseService' ];

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
            var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});
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
            deferred.resolve(response);
        })
        .error(function (data, status) {
            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'Papers get all error';
            var code = data && data.error && data.error.code ? data.error.code : 403;
            var url = Routing.generate('ujm_sequence_error', {message: msg, code: code});

            /*$window.location = url;*/
        });

    return deferred.promise;
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
            deferred.resolve(response);
        })
        .error(function (data, status) {
            deferred.reject([]);
        });

    return deferred.promise;
};

// Register service into AngularJS
angular
    .module('Paper')
    .service('PaperService', PaperService);