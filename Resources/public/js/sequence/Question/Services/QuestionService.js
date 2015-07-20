/**
 * Question service
 */
(function () {
    'use strict';

    angular.module('Question').factory('QuestionService', [
        '$http',
        '$filter',
        '$q',
        function QuestionService($http, $filter, $q) {       
           

            return {
               
                /**
                 * Get all available questions
                 * @returns promise
                 */
                getAll : function (){
                    var deferred = $q.defer(); 
                    $http
                        .get(
                            Routing.generate('ujm_step_get_available_questions')
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('QuestionService, getAll method error');
                            console.log(status);
                            console.log(data);
                            deferred.reject([]);
                        });
                        
                     return deferred.promise;
                },
                /**
                 * get questions for a step
                 * @param sequence
                 * @returns 
                 */
                getStepQuestions : function (step){
                    var deferred = $q.defer();                    
                    $http
                        .get(
                            Routing.generate('ujm_step_get_questions', { id : step.id })
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('QuestionService, getStepQuestions method error');
                            console.log(status);
                            console.log(data);
                        });
                        return deferred.promise;
                }
            };
        }
    ]);
})();