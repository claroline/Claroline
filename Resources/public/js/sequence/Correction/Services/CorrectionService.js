/**
 * CorrectionService
 */
(function () {
    'use strict';

    angular.module('Correction').factory('CorrectionService', [
        '$window',
        '$http',
        '$filter',
        '$q',
        function PaperService($window, $http, $filter, $q) {       
           

            return {
               getQuestionScore:function(question, paper){
                   var solutions = question.solutions;
                   var hints = question.hints;
                   var score = 0.0;
                   for (var i = 0; i < paper.questions.length; i++){
                       if(paper.questions[i].id === question.id.toString()){
                           
                       }
                   }
                   
                   return score;
                   
               },
               /**
                 * Get one paper details
                 * @param {type} exoId
                 * @param {type} paperId
                 * @returns {$q@call;defer.promise}
                 */
                getOne: function (exoId, paperId) {
                    var deferred = $q.defer();
                    $http
                            .get(
                                    Routing.generate('exercise_paper', {exerciseId: exoId, paperId: paperId})
                                    )
                            .success(function (response) {
                                deferred.resolve(response);
                            })
                            .error(function (data, status) {
                                console.log('PapersService, getOne method error');
                                deferred.reject([]);
                                //console.log(exoId + ' ' + paperId);
                                //console.log(data);
                                var url = Routing.generate('ujm_sequence_error', {message: data.error.message, code: data.error.code});
                                $window.location = url;
                            });

                    return deferred.promise;
                }
               
                
            };
        }
    ]);
})();