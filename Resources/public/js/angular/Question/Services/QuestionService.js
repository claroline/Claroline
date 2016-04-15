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

// Register service into AngularJS
angular
    .module('Question')
    .service('QuestionService', QuestionService);
