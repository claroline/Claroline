
(function () {
    'use strict';

    angular.module('Question').directive('matchQuestionProposal', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'MatchQuestionProposalCtrl',
                controllerAs: 'matchQuestionProposalCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Question/Partials/proposal.html',
                scope: {
                    step: '=',
                    question: '=',
                    selfRemove: "&"
                },
                link: function (scope, element, attr, matchQuestionProposalCtrl) {
                    matchQuestionProposalCtrl.setProposal(attr.proposal);
                    matchQuestionProposalCtrl.init(attr.proposal);
                    
                    if (element.attr("id").substr(0,9) === "draggable") {
                        console.log(element.attr("id").substr(0,9));
                        jsPlumb.makeSource(element, {
                            anchor: "Right",
                            cssClass: "endPoints",
                            isSource: true
                        });
                        
                        jsPlumb.addEndpoint(element, {
                            anchor: 'RightMiddle',
                            cssClass: "endPoints",
                            isSource: true,
                            maxConnections: -1
                        });
                    }
                    else {
                        console.log(element.attr("id").substr(0,9));
                        jsPlumb.makeTarget(element, {
                            anchor: "Left",
                            cssClass: "endPoints",
                            isTarget: true
                        });
                        
                        jsPlumb.addEndpoint(element, {
                            anchor: 'LeftMiddle',
                            cssClass: "endPoints",
                            isTarget: true,
                            maxConnections: -1
                        });
                    }
                    
                    
                    /*
                    // $(element)

                    $timeout(function () {
                        $(".origin").each(function () {
                            console.log('jojo ');
                        });
                    }, 1000);
                    
                    $(".droppable").each(function () {
                        console.log("trucbidule");
                    });

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


