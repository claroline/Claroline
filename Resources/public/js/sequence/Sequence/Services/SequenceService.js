/**
 * Exercise player service
 */
(function () {
    'use strict';

    angular.module('Sequence').factory('SequenceService', [
        '$http',
        '$filter',
        '$q',
        function PlayerService($http, $filter, $q) {       
           

            return {
               
                /**
                 * Update the sequence
                 * @param sequence
                 * @returns 
                 */
                update : function (sequence){
                    var deferred = $q.defer();                    
                    // sequence constructor
                    function Sequence(sequence){
                        var ujm_sequence = {
                            name: sequence.name,
                            description: sequence.description,
                            startDate: new Date(sequence.startDate),
                            endDate: new Date(sequence.endDate)
                        };
                        
                        return ujm_sequence;
                    }
                    
                    var updated = new Sequence(sequence);
                   
                    $http
                        .put(
                            Routing.generate('ujm_sequence_update', { id : sequence.id }),
                            {
                                sequence_type: updated
                            }
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('sequence service, update method error');
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