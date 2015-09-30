/**
 * Exercise player service
 */
(function () {
    'use strict';

    angular.module('Sequence').factory('SequenceService', [
        '$http',
        '$filter',
        '$q',
        function SequenceService($http, $filter, $q) {       
          
            return {
                
                /**
                 * Each time a student navigate through question / step we have to record the current answer
                 * the answer object might have an id in this case we update the previous record else we create a new one
                 * @param {number} exercise id (= step id)
                 * @param {object} answer
                 * @returns deferred
                 */
                recordAnswer : function(exo_id, answer){
                    var deferred = $q.defer();
                    $http
                        .put(
                            // fake for testing (param converter needs real object!)
                            Routing.generate('ujm_sequence_record_step', {exo_id : 9, question_id : 12}), {data: answer}
                            // real one when API will be ready
                            //Routing.generate('ujm_sequence_record_step', {exo_id : exo_id, question_id : answer.question.id}), {data: answer}
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('sequence service, recordAnswer method error');
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