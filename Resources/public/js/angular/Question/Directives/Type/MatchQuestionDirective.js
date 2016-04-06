angular.module('Question').directive('matchQuestion', [
    '$timeout',
    function ($timeout) {
        return {
            restrict: 'E',
            replace: true,
            controller: 'MatchQuestionCtrl',
            controllerAs: 'matchQuestionCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/match.html',
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
                    if (scope.question.toBind) {
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

                    } else {

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
                        
                        $(".draggable").each(function () {
                            var id = $(this)[0].id.replace("div", "drag_handle");
                            $(this).draggable({
                                handle: "#" + id
                            });
                        });

                        matchQuestionCtrl.addPreviousDroppedItems();
                    }

                }.bind(this));
            }
        };
    }
]);
