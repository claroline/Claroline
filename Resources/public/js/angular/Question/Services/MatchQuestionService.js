/**
 * Match Question service
 * Since ther is a lot of DOM manipulation
 * with MatchQuestion toDrag and toBind
 * we deport all that stuff in this service to keep Controller as slim as possible
 */
angular.module('Question').factory('MatchQuestionService', [
    function MatchQuestionService() {

        return {
            initBindMatchQuestion: function () {
                jsPlumb.setContainer($("body"));

                // source elements
                $(".origin").each(function () {
                    jsPlumb.addEndpoint(this, {
                        anchor: 'RightMiddle',
                        cssClass: "endPoints",
                        isSource: true,
                        maxConnections: -1
                    });
                });

                // target elements
                $(".droppable").each(function () {
                    jsPlumb.addEndpoint(this, {
                        anchor: 'LeftMiddle',
                        cssClass: "endPoints",
                        isTarget: true,
                        maxConnections: -1
                    });
                });

                // defaults parameters for all connections
                jsPlumb.importDefaults({
                    Anchors: ["RightMiddle", "LeftMiddle"],
                    ConnectionsDetachable: false,
                    Connector: "Straight",
                    DropOptions: {tolerance: "touch"},
                    HoverPaintStyle: {strokeStyle: "red"},
                    LogEnabled: true,
                    PaintStyle: {strokeStyle: "#777", lineWidth: 4}
                });

                jsPlumb.detachEveryConnection();
            },

            initDragMatchQuestion: function () {
                jsPlumb.detachEveryConnection();
                jsPlumb.deleteEveryEndpoint();

                // activate drag on each proposal
                $(".draggable").each(function () {
                    $(this).draggable({
                        cursor: 'move',
                        revert: 'invalid',
                        helper: 'clone',
                        zIndex: 10000,
                        cursorAt: {top:5, left:5}
                    });
                });

                $(".droppable").each(function () {
                    // in exercice, if go on previous question, just visual aspect
                    if ($(this).children().length > 2) {
                        var childrens = $(this).children().length;
                        var i = 2;
                        // replace proposal in the div dragDropped
                        for (i = 2; i < childrens; i++) {
                            $(this).children(".dragDropped").prepend($(this).children().last().clone());
                            $(this).children().last().remove();
                        }
                        // active the css class when drag dropped
                        $(this).addClass("state-highlight");
                        $(this).children(".dragDropped").children().each(function () {
                            // add the image for delete drag
                            var id = $(this).attr('id');
                            var idNumber = id.replace('draggable_', '');
                            //balisesLiDropped[idNumber] = $(this);
                            var idDrag = $(this).attr('id');
                            $(this).append("<a class='fa fa-trash' id=reset" + idDrag + "></a>");
                        });
                    }

                    $(this).droppable({
                        tolerance: "pointer",
                        activeClass: "state-hover",
                        hoverClass: "state-active"
                    });

                    $(".origin").each(function () {
                        // for exercice, if go on previous question
                        if ($(this).children().children().length === 0) {
                            var id = $(this).attr('id');
                            // var idNumber = id.replace('div_', '');

                            // make the right apprearence for colunmm of label and proposal
                            //$(this).children().append(balisesLiDropped[idNumber].clone());
                            $(this).children().children().children("a").remove();
                            $(this).children().children().removeClass();
                            $(this).children().children().addClass("draggable ui-draggable ui-draggable-disabled ui-state-disabled");
                            var idDrag = id.replace('div', 'draggable');
                            idDrag = "#" + idDrag;
                            // discolor the text
                            $(idDrag).fadeTo(100, 0.3);

                            //@TODO check if this is necessary...

                            /*var droppable = balisesLiDropped[idNumber].parent().parent();
                             // option for draggable
                             activeDraggable($(this).children().children());
                             $(idDrag).draggable("disable");
                             // to remove a drag drapped
                             disableDrag(idDrag, droppable);*/
                        }

                        $(this).droppable({
                            tolerance: "pointer"
                        });
                    });
                });
            }

        };
    }
]);