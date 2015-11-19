
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
                    
                    console.log(element);
                    console.log(element.attr);
                    
                    if (element.attr("id").substr(0,9) === "draggable") {
                        jsPlumb.addEndpoint(element, {
                            anchor: 'RightMiddle',
                            cssClass: "endPoints",
                            isSource: true,
                            maxConnections: -1
                        });
                    }
                    else {
                        jsPlumb.addEndpoint(element, {
                            anchor: 'LeftMiddle',
                            cssClass: "endPoints",
                            isTarget: true,
                            maxConnections: -1
                        });
                    }
                    
                    jsPlumb.detachEveryConnection();
                }
            };
        }
    ]);
})();


