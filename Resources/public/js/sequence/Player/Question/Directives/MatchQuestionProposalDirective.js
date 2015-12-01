
(function () {
    'use strict';

    angular.module('Question').directive('matchQuestionProposal', [
        '$timeout',
        function ($timeout) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'MatchQuestionProposalCtrl',
                controllerAs: 'matchQuestionProposalCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Question/Partials/proposal.html',
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
                    
                    $timeout(function init() {
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
                    });
                }
            };
        }
    ]);
})();


