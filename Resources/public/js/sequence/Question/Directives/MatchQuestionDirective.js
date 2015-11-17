
(function () {
    'use strict';

    angular.module('Question').directive('matchQuestion', [
        '$timeout',
        function ($timeout) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'MatchQuestionCtrl',
                controllerAs: 'matchQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Question/Partials/match.question.html',
                scope: {
                    step: '=',
                    question: '=',
                    selfRemove: "&"
                },
                link: function (scope, element, attr, matchQuestionCtrl) {
                    matchQuestionCtrl.setQuestion(scope.question);
                    matchQuestionCtrl.init(scope.question);
                    
                    $("#resetAll").click(function() {
                        jsPlumb.detachEveryConnection();
                    });
                    
                    // $(element)
/*
                    $timeout(function () {
                        $(".origin").each(function () {
                            console.log('jojo ');
                        });
                    }, 1000);
                    
                    $(".droppable").each(function () {
                        console.log("trucbidule");
                    });
                    
                    $(".all").each(function () {
                        console.log("écris bordel");
                    });
                    console.log("modif");
*/
                    /*var myelements = element[0].getElementsByClassName('origin');
                     
                     console.log(myelements.length);
                     
                     for (var i=0; i<myelements.length; i++) {
                     console.log(myelements[i]);
                     }
                     jsPlumb.makeSource(element[0].getElementsByClassName('origin'), {
                     anchor: "Right",
                     cssClass: "endPoints",
                     isSource: true
                     });*/
                    /*
                     jsPlumb.draggable(element, {
                     start: function () {
                     console.log('drag start');
                     },
                     drag: function (event, ui) {
                     console.log('drag');
                     },
                     stop: function (event, ui) {
                     console.log('drag stop');
                     }
                     });*/
                }
            };
        }
    ]);
})();


