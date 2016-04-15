/**
 * Question service
 * @param {Object} $http
 * @param {Object} $q
 * @param {Object} UserPaperService
 */
var QuestionService = function QuestionService($http, $q) {
    this.$http = $http;
    this.$q    = $q;
};

// Set up dependency injection
QuestionService.$inject = [ '$http', '$q' ];

/**
 * Get a hint penalty
 * @param {type} collection array of penalty
 * @param {type} searched searched id
 * @returns number
 * @deprecated - Only used to calculate scores in Papers, but we will need to get the score from server
 */
QuestionService.prototype.getHintPenalty = function getHintPenalty(collection, searched) {
    for (var i = 0; i < collection.length; i++) {
        if (collection[i].id === searched) {
            return collection[i].penalty;
        }
    }
};

/**
 * Used for displaying in-context question feedback and solutions
 * @param   {Object} question
 * @returns {Object}
 */
QuestionService.prototype.getSolutions = function getSolutions(question) {
    var deferred = this.$q.defer();
    this.$http
        .get(
            Routing.generate('get_question_solutions', { id: question.id })
        )
        .success(function (response) {
            deferred.resolve(response);
        })
        .error(function (data, status) {
            deferred.reject([]);
            var msg = data && data.error && data.error.message ? data.error.message : 'QuestionService get solutions error';
            var code = data && data.error && data.error.code ? data.error.code : 400;
            var url = Routing.generate('ujm_sequence_error', {message:msg, code:code});
            //$window.location = url;
        });

    return deferred.promise;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('QuestionService', QuestionService);
