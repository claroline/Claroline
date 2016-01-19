
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
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Question/Partials/match.question.html',
                scope: {
                    step: '=',
                    question: '=',
                    canSeeFeedback: '='
                },
                link: function (scope, element, attr, matchQuestionCtrl) {

                    matchQuestionCtrl.init(scope.question);
                    // init jsPlumb dom elements
                    $timeout(function () {
                        // MatchQuestion sub type is ToBind
                        if (scope.question.subType === 'toBind') {

                            matchQuestionCtrl.initMatchQuestionJsPlumb('bind');
                            $("#resetAll").click(function () {
                                matchQuestionCtrl.reset('bind');
                            });

                            jsPlumb.bind("beforeDrop", function (info) {
                                return matchQuestionCtrl.handleBeforDrop(info);
                            });

                            // remove one connection
                            jsPlumb.bind("click", function (connection) {
                                matchQuestionCtrl.removeConnection(connection);
                            });

                            matchQuestionCtrl.addPreviousConnections();

                        } else if (scope.question.subType === 'toDrag') {

                            matchQuestionCtrl.initMatchQuestionJsPlumb('drag');

                            // reset all elements
                            $("#resetAll").click(function () {
                                matchQuestionCtrl.reset('drag');
                            });

                            $(".droppable").each(function () {
                                $(this).on("drop", function (event, ui) {
                                    matchQuestionCtrl.handleDragMatchQuestionDrop(event, ui);
                                });
                            });
                            
                            matchQuestionCtrl.addPreviousDroppedItems();
                        }

                    }.bind(this));
                }
            };
        }
    ]);
})();


