/**
 * Page Service
 */
(function () {
    'use strict';

    angular.module('Step').factory('StepService', [
        '$http',
        '$filter',
        '$q',
        function StepService($http, $filter, $q) {
           
            return {
               
                /**
                 * Update exercise player steps
                 * @param player
                 * @returns 
                 */
                update : function (steps){
                    var deferred = $q.defer(); 
                    var sequenceId = steps[0].sequenceId;
                    $http
                        .post(
                            Routing.generate('ujm_steps_update', { id : sequenceId}), {steps: steps}
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('Step service, update method error');
                            console.log(status);
                            console.log(data);
                        });
                        
                     return deferred.promise;
                }
            };
        }
    ]);
})();