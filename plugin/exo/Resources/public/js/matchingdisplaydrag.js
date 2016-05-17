var responses = [];
var balisesLiDropped = [];

$(function() {

    // for reset all connections
    $("#resetAll").click(function() {
        // initialise the part of proposal
        $(".origin").each(function(){
            if($(this).find('.draggable').attr('style')) {
                $(this).find('.draggable').removeAttr('style');
                $(this).find('.draggable').removeAttr('aria-disabled');
                $(this).find('.draggable').draggable("enable");
                var idProposal = $(this).attr("id");
                // reset table of response
                idProposal = idProposal.replace('div_', '');
                responses[idProposal]= 'NULL';
            }
        });
        // reset the part of label
        $(".droppable").each(function(){
            if($(this).find(".dragDropped").children()) {
                $(this).removeClass('state-highlight');
                $(this).find(".dragDropped").children().remove();
            }
        });
        // reset response in jsonResponse balise
        dragStop();
    });

    // for visual, extend gray rectangle for label
    if($(".draggable").width() > $(".droppable").width()) {
        var $widthDraggable = $(".draggable").width();
        var $widthDroppable = $widthDraggable;
        $(".droppable").width($widthDroppable * 1.5);
    }

    // for active the draggable on each proposal
    $(".draggable").each(function() {
        activeDraggable($(this));
    });

    $(".droppable").each(function() {
        // in exercice, if go on previous question, just visual aspect
        if($(this).children().length > 2) {
            var childrens = $(this).children().length;
            var i=2;
            // replace proposal in the div dragDropped
            for(i=2; i<childrens; i++) {
                $(this).children(".dragDropped").prepend($(this).children().last().clone());
                $(this).children().last().remove();
            }
            // active the css class when drag dropped
            $(this).addClass("state-highlight");
            $(this).children(".dragDropped").children().each(function() {
                // add the image for delete drag
                var id = $(this).attr('id');
                var idNumber = id.replace('draggable_', '');
                balisesLiDropped[idNumber] = $(this);
                var idDrag = $(this).attr('id');
                $(this).append("<a class='fa fa-trash' id=reset"+idDrag+"></a>");
            });
        }
        $(this).droppable({
            tolerance: "pointer",
            activeClass: "state-hover",
            hoverClass: "state-active",
            drop: function(event, ui) {
                var idLabel = $(this).attr('id');
                idLabel = idLabel.replace('droppable_', '');
                var idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                // for register responses
                if (idProposal) {
                    responses[idProposal] = idLabel;
                }
                idProposal = ui.draggable.attr("id");
                $(this).addClass("state-highlight");
                // clone the drag in drop
                $(this).children(".dragDropped").append($(ui.helper).clone().removeClass("draggable ui-draggable ui-draggable-dragging")
                        .removeAttr('style').css("list-style-type","none").addClass(idProposal));

                $("."+idProposal).attr('id', idProposal);
                var idDrag = "#"+idProposal;
                $(this).find(".dragDropped").children(idDrag).append("<a class='fa fa-trash' id=reset"+idDrag+"></a>");
                // discolor the text
                $(idDrag).draggable("disable");
                $(idDrag).fadeTo(100, 0.3);
                disableDrag(idDrag, $(this));
            }
        });
    });

    $(".origin").each(function() {
        // for exercice, if go on previous question
        if($(this).children().children().length === 0) {
            var id = $(this).attr('id');
            var idNumber = id.replace('div_', '');

            // make the right apprearence for colunmm of label and proposal
            $(this).children().append(balisesLiDropped[idNumber].clone());
            $(this).children().children().children("a").remove();
            $(this).children().children().removeClass();
            $(this).children().children().addClass("draggable ui-draggable ui-draggable-disabled ui-state-disabled");
            var idDrag = id.replace('div', 'draggable');
            idDrag = "#"+idDrag;
            // discolor the text
            $(idDrag).fadeTo(100, 0.3);
            var droppable = balisesLiDropped[idNumber].parent().parent();
            // option for draggable
            activeDraggable($(this).children().children());
            $(idDrag).draggable("disable");
            // to remove a drag drapped
            disableDrag(idDrag, droppable);
        }

        $(this).droppable({
            tolerance: "pointer"
        });
    });
});

function activeDraggable(draggable) {
    draggable.draggable({
        cursor: 'move',
        revert: 'invalid',
        helper: 'clone',
        stop: function(event, ui) {
             // update response in the balise
            dragStop();
        }
    });
}

function disableDrag(idDrag, idDrop) {
    var draggableDropped = idDrop.children(".dragDropped").children(idDrag);

    // removes a drag dropped
    idDrop.children(".dragDropped").children(idDrag).children().last().click(function() {
        if(idDrop.children(".dragDropped").children().length <= 1) {
            idDrop.removeClass("state-highlight");
        }
        // update the response table
        removeDragTable(idDrag, draggableDropped);
    });
}

function removeDragTable(idDrag, draggableDropped) {
    var idProposal = idDrag;
    idProposal = idProposal.replace('#draggable_', '');
    if (idProposal) {
        responses[idProposal] = 'NULL';
    }
    // update response in the balise
    dragStop();
    draggableDropped.remove();
    // resets of draggable
    $(idDrag).draggable("enable");
    $(idDrag).fadeTo(100, 1);
}

function dragStop() {
    var resp = '';
    $.each(responses, function(key, value) {
        if (value) {
            resp = resp + key + ',' + value + ';';
        }
    });
    $('#jsonResponse').val(resp);
}

function placeProposal(idLabel, idProposal) {
    $("#draggable_" + idProposal).appendTo('#droppable_' + idLabel);
    responses[idProposal] = idLabel;
    // update response in the balise
    dragStop();
}
