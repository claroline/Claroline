var responses = [];

$(function() {
    
    if($(".draggable").width() > $(".droppable").width()) {
        var $widthDraggable = $(".draggable").width();
        var $widthDroppable = $widthDraggable;
        $(".droppable").width($widthDroppable * 1.5);
    }

    $(".draggable").each(function() {
        $(this).draggable({
            cursor: 'move',
            revert: 'invalid',
            helper: 'clone',
            stop: function(event, ui) {
//dump(responses);
                dragStop();
            },
        });
    });
    
    $(".droppable").each(function() {
        $(this).droppable({
            tolerance: "pointer",
            activeClass: "state-hover",
            hoverClass: "state-active",
            drop: function(event, ui) {
                idLabel = $(this).attr('id');
                idLabel = idLabel.replace('droppable_', '');
                idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                if (idProposal) {
                    responses[idProposal] = idLabel;
                }
                idProposal = ui.draggable.attr("id");
                $(this).addClass("state-highlight");
                $(this).children(".dragDropped").append($(ui.helper).clone().removeClass("draggable ui-draggable ui-draggable-dragging")
                        .removeAttr('style').css("list-style-type","none").addClass(idProposal));
                $("."+idProposal).attr('id', idProposal);
                if(ui.draggable.height() > $(this).height()) {
                    $(this).height(ui.draggable.height() * 1.5);
                }
                var idDrag = "#"+idProposal;
                $(this).find(".dragDropped").children(idDrag).append("<a id=reset"+idDrag+"><img src="+deleteImage()+" /></a>");
                //desactivate the drag
                $(idDrag).draggable("disable");
                // discolor the text
                $(idDrag).fadeTo(100, 0.3);
                
                var draggableDropped = $(this).children(".dragDropped").children(idDrag);
                if($(this).children(".dragDropped").children(idDrag).length <= 2) {
                    $(this).children(".dragDropped").children(idDrag).children().last().click(function() {
                        if($(this).parent().parent().children().length <=1) {
                            $(this).parent().parent().parent().removeClass("state-highlight");
                        }
                        removeDrag(ui, idDrag, draggableDropped);
                    });
                } else {
                    $(this).children(".dragDropped").children(idDrag).children().click(function() {
                        if($(this).parent().children().length <=1) {
                            $(this).parent().parent().parent().removeClass("state-highlight");
                        }
                        removeDrag(ui, idDrag, draggableDropped);
                    });
                }
            },
        });
    });

    $(".origin").each(function() {
        $(this).droppable({
            tolerance: "pointer",
        });
    });
});

function removeDrag(ui, idDrag, draggableDropped) {
    idProposal = ui.draggable.attr("id");
    idProposal = idProposal.replace('draggable_', '');
    if (idProposal) {
        responses[idProposal] = 'NULL';
    }
    dragStop();
    
    draggableDropped.remove();
    // reinitalisation of draggable
    $(idDrag).draggable("enable");
    $(idDrag).fadeTo(100, 1);
}

function dragStop() {
    var resp = '';
    $.each(responses, function(key, value) {
        if (value) {
            resp = resp + key + '-' + value + ';';
        }
    });
    $('#jsonResponse').val(resp);
}

function placeProposal(idLabel, idProposal) {
    $("#draggable_" + idProposal).appendTo('#droppable_' + idLabel);
    responses[idProposal] = idLabel;
    dragStop();
}

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }
    alert(out);
}