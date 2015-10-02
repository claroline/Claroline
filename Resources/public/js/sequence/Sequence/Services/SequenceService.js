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
                 * @returns promise
                 */
                recordAnswer : function(exoId, answer){
                    var deferred = $q.defer();
                    $http
                        .put(
                            // fake for testing (param converter needs real object!)
                            Routing.generate('ujm_sequence_record_step', {exo_id : 9, question_id : 12}), {data: answer}
                            // real one when API will be ready
                            //Routing.generate('ujm_sequence_record_step', {exo_id : exoId, question_id : answer.question.id}), {data: answer}
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
                },
                /**
                 * End the sequence by setting the paper data
                 * @param {type} exoId
                 * @param {type} studentPaper
                 * @returns promise
                 */
                endSequence : function (exoId, studentPaper){
                    var deferred = $q.defer();
                    $http
                        .put(
                            Routing.generate('ujm_sequence_end', {id : exoId}), {paper: studentPaper}
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('sequence service, endSequence method error');
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