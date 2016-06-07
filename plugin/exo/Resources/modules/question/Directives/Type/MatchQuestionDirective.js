import AbstractQuestionDirective from './AbstractQuestionDirective'
import match from './../../Partials/Type/match.html'

/**
 * Match Question Directive
 * Manages Question of types Match
 *
 * @returns {Object}
 * @constructor
 */
function MatchQuestionDirective(FeedbackService, $timeout, MatchQuestionService) {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'MatchQuestionCtrl',
        controllerAs: 'matchQuestionCtrl',
        template: match,
        link: {
            post: function postLink(scope, element, attr, controller) {
                // init jsPlumb dom elements
                $timeout(function () {
                    // MatchQuestion sub type is ToBind
                    if (controller.question.toBind) {
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
                                hoverPaintStyle:{ strokeStyle:"#FC0000", lineWidth: 6 }
                            }
                        });

                        MatchQuestionService.initBindMatchQuestion();

                        jsPlumb.bind("beforeDrop", function (info) {
                            return controller.handleBeforeDrop(info);
                        });

                        // remove one connection
                        jsPlumb.bind("click", function (connection) {
                            var deletable = false;
                            if (connection._jsPlumb.hoverPaintStyle.strokeStyle === "#FC0000") {
                                controller.removeConnection(connection);
                            }
                        });

                        controller.addPreviousConnections();

                    } else {
                        MatchQuestionService.initDragMatchQuestion();

                        $(".droppable").each(function () {
                            $(this).on("drop", function (event, ui) {
                                controller.handleDragMatchQuestionDrop(event, ui);
                            });
                        });

                        if (controller.question.typeMatch === 3) {
                            $(".draggable").each(function () {
                                var id = $(this)[0].id.replace("div", "drag_handle");
                                $(this).draggable({
                                    handle: "#" + id
                                });
                            });
                        }

                        controller.addPreviousDroppedItems();
                    }

                    // Manually show feedback (as we override the default postLink method)
                    if (FeedbackService.isVisible()) {
                        controller.onFeedbackShow();
                    }
                }.bind(this));

                // On directive destroy, remove events
                scope.$on('$destroy', function handleDestroyEvent() {
                    // TODO : remove drag'n'drop events
                    jsPlumb.detachEveryConnection();
                    jsPlumb.deleteEveryEndpoint();
                });
            }
        }
    });
}

// Extends AbstractQuestionDirective
MatchQuestionDirective.prototype = Object.create(AbstractQuestionDirective.prototype);

export default MatchQuestionDirective
