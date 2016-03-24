/**
 * Papers service
 */
angular.module('Paper').factory('PaperService', [
    '$http',
    '$q',
    'ExerciseService',
    function PaperService($http, $q, ExerciseService) {
        return {
            /**
             * Get one paper details
             * @param   {String} id
             * @returns {Promise}
             */
            get: function get(id) {
                var exercise = ExerciseService.getExercise();

                var deferred = $q.defer();
                $http
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
            },

            /**
             * Get all papers for an Exercise
             * @returns {Promise}
             */
            getAll: function getAll() {
                var exercise = ExerciseService.getExercise();

                var deferred = $q.defer();
                $http
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
            },

            /**
             * Delete all papers of an Exercise
             */
            deleteAll: function deleteAll(papers) {
                var exercise = ExerciseService.getExercise();

                var deferred = $q.defer();
                $http
                    .delete(Routing.generate('ujm_exercise_delete_papers', { id: exercise.id }))
                    .success(function (response) {
                        papers.splice(0, papers.length); // Empty the Papers list
                        deferred.resolve(response);
                    })
                    .error(function (data, status) {
                        deferred.reject([]);
                    });

                return deferred.promise;
            }
        };
    }
]);