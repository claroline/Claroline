/**
 * Match Question Directive
 * Manages Question of types Match
 *
 * @returns {object}
 * @constructor
 */
var MatchQuestionDirective = function MatchQuestionDirective($timeout, MatchQuestionService) {
    return {
        restrict: 'E',
        replace: true,
        controller: 'MatchQuestionCtrl',
        controllerAs: 'matchQuestionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/match.html',
        scope: {
            question     : '=',
            questionPaper: '='
        },
        link: function (scope, element, attr, matchQuestionCtrl) {
            // init jsPlumb dom elements
            $timeout(function () {
                // MatchQuestion sub type is ToBind
                if (matchQuestionCtrl.question.toBind) {
                    MatchQuestionService.initBindMatchQuestion();

                    jsPlumb.bind("beforeDrop", function (info) {
                        return matchQuestionCtrl.handleBeforeDrop(info);
                    });

                    // remove one connection
                    jsPlumb.bind("click", function (connection) {
                        matchQuestionCtrl.removeConnection(connection);
                    });

                    matchQuestionCtrl.addPreviousConnections();

                } else {
                    MatchQuestionService.initDragMatchQuestion();

                    $(".droppable").each(function () {
                        $(this).on("drop", function (event, ui) {
                            matchQuestionCtrl.handleDragMatchQuestionDrop(event, ui);
                        });
                    });

                    if (matchQuestionCtrl.question.typeMatch === 3) {
                        $(".draggable").each(function () {
                            var id = $(this)[0].id.replace("div", "drag_handle");
                            $(this).draggable({
                                handle: "#" + id
                            });
                        });
                    }

                    matchQuestionCtrl.addPreviousDroppedItems();
                }

            }.bind(this));
        }
    };
};

// Set up dependency injection
MatchQuestionDirective.$inject = [ '$timeout', 'MatchQuestionService' ];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('matchQuestion', MatchQuestionDirective);
