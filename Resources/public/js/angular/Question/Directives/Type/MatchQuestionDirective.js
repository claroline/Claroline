/**
 * Match Question Directive
 * Manages Question of types Match
 *
 * @returns {object}
 * @constructor
 */
var MatchQuestionDirective = function MatchQuestionDirective($timeout) {
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
                    matchQuestionCtrl.initMatchQuestionJsPlumb('bind');

                    jsPlumb.bind("beforeDrop", function (info) {
                        return matchQuestionCtrl.handleBeforeDrop(info);
                    });

                    // remove one connection
                    jsPlumb.bind("click", function (connection) {
                        matchQuestionCtrl.removeConnection(connection);
                    });

                    matchQuestionCtrl.addPreviousConnections();

                } else {
                    matchQuestionCtrl.initMatchQuestionJsPlumb('drag');

                    $(".droppable").each(function () {
                        $(this).on("drop", function (event, ui) {
                            matchQuestionCtrl.handleDragMatchQuestionDrop(event, ui);
                        });
                    });

                    matchQuestionCtrl.addPreviousDroppedItems();
                }
            });

            // Destroy JSPlumb events
            scope.$on('$destroy', function () {
                jsPlumb.detachEveryConnection();
                jsPlumb.deleteEveryEndpoint();
            });
        }
    };
};

// Set up dependency injection
MatchQuestionDirective.$inject = [ '$timeout' ];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('matchQuestion', MatchQuestionDirective);
