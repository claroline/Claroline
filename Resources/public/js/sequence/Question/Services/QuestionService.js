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
                 * Get an hint
                 * @returns promise
                 */
                getHint : function (_id){
                    var deferred = $q.defer(); 
                    $http
                        .get(
                            Routing.generate('ujm_get_hint_content', { id : _id })
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('QuestionService, getHint method error');
                            console.log(status);
                            console.log(data);
                            deferred.reject([]);
                        });
                        
                     return deferred.promise;
                }
                      
            };
        }
    ]);
})();