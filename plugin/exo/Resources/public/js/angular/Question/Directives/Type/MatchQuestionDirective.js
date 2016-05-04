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
                    jsPlumb.registerConnectionTypes({
                        "right": {
                            paintStyle:{ strokeStyle:"#5CB85C", lineWidth: 5  },
                            hoverPaintStyle:{ strokeStyle:"green", lineWidth: 6 }
                        },
                        "wrong": {
                            paintStyle:{ strokeStyle:"#D9534F", lineWidth: 5 },
                            hoverPaintStyle:{ strokeStyle:"red", lineWidth: 6 }
                        },
                        "default": {
                            paintStyle:{ strokeStyle:"grey", lineWidth: 5 },
                            hoverPaintStyle:{ strokeStyle:"grey", lineWidth: 6 }
                        }
                    });
    
                    MatchQuestionService.initBindMatchQuestion();

                    jsPlumb.bind("beforeDrop", function (info) {
                        return matchQuestionCtrl.handleBeforeDrop(info);
                    });

                    // remove one connection
                    jsPlumb.bind("click", function (connection) {
                        var deletable = false;
                        for (var i=0; i<connection._jsPlumb.types.length; i++) {
                            if (connection._jsPlumb.types[i] === "default") {
                                deletable = true;
                            }
                        }
                        if (deletable) {
                            matchQuestionCtrl.removeConnection(connection);
                        }
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

            // On directive destroy, remove events
            scope.$on('$destroy', function handleDestroyEvent() {
                // TODO : remove drag'n'drop events
                jsPlumb.detachEveryConnection();
                jsPlumb.deleteEveryEndpoint();
            });
        }
    };
};

// Set up dependency injection
MatchQuestionDirective.$inject = [ '$timeout', 'MatchQuestionService' ];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('matchQuestion', MatchQuestionDirective);
