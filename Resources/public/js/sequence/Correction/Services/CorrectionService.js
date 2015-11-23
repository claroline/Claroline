/**
 * CorrectionService
 */
(function () {
    'use strict';

    angular.module('Correction').factory('CorrectionService', [
        '$http',
        '$filter',
        '$q',
        function PaperService($http, $filter, $q) {       
           

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
                   
               }
               
                
            };
        }
    ]);
})();