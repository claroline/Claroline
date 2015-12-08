/**
 * Papers service
 */
(function () {
    'use strict';

    angular.module('PapersApp').factory('PapersService', [
        '$http',
        '$filter',
        '$q',
        '$window',
        function PapersService($http, $filter, $q, $window) {

            this.displayRetryExerciseLink = false;

            return {
                /**
                 * get all papers for an exercise
                 * @param {string} id
                 * @returns {$q@call;defer.promise}
                 */
                getAll: function (id, user) {
                    var deferred = $q.defer();
                    var url = '';
                    if (user.admin) {
                        url = Routing.generate('exercise_papers_admin', {id: id})
                    } else {
                        url = Routing.generate('exercise_papers', {id: id})
                    }
                    $http
                            .get(
                                    url
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                /**
                 * Get paper's exercise info
                 * @param {type} id
                 * @returns {$q@call;defer.promise}
                 */
                getSequence: function (id) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('exercise_get_minimal', {id: id})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                getConnectedUser: function (id) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('paper_get_connected_user', {id: id})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                deferred.reject([]);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                },
                countFinishedPaper:function(id){
                    var deferred = $q.defer();
                    $http
                            .get(
                                Routing.generate('exercise_papers_count', {id:id})
                            )
                            .success(function (response){
                                deferred.resolve(response);
                            })
                            .error(function(data, status){
                                 deferred.reject([]);    
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });
                    return deferred.promise;
                },
                /**
                 * Checks if current user can replay the exercise
                 * Only used if current user is not admin and exercise maxAttempts property is != 0
                 * @param {number} max
                 * @param {Object} paper list
                 * @returns {Boolean}
                 */
                userCanReplayExercise: function (max, papers) {
                    var lastPaperNumber = this.getPaperMaxNumber(papers);
                    return lastPaperNumber < max;
                },
                getPaperMaxNumber: function (papers) {
                    var max = 0;
                    for (var i = 0; i < papers.length; i++) {
                        if (max < papers[i].number && papers[i].end !== '' && papers[i].end !== undefined && !papers[i].interrupted) {
                            max = papers[i].number;
                        }
                    }
                    return max;
                }
            };
        }
    ]);
})();