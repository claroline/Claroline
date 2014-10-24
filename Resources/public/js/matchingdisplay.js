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

    $(".origin").each(function() {
        $(this).droppable({
            tolerance: "pointer",
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
                $(this).find(".dragDropped").children(idDrag).append("<a id=reset"+idDrag+"><img src=http://127.0.0.1/Claroline/web/bundles/ujmexo/images/delete.png /></a>");
                //desactivate the drag
                $(idDrag).draggable("disable");
                // discolor the text
                $(idDrag).fadeTo(100, 0.3);
                
                if($(this).children(".dragDropped").children(idDrag).length <= 2) {
                    $(this).children(".dragDropped").children(idDrag).children().last().click(function() {
                    if($(this).parent().parent().children().length <=1) {
                        
                        $(this).parent().parent().parent().removeClass("state-highlight");
                    }
                    idProposal = ui.draggable.attr("id");
                    idProposal = idProposal.replace('draggable_', '');
                    if (idProposal) {
                        responses[idProposal] = 'NULL';
                    }
                    dragStop();
                    
                    $(this).parent().remove();
                    // reinitalisation of draggable
                    $(idDrag).draggable("enable");
                    $(idDrag).fadeTo(100, 1);
                });
                } else {
                    $(this).children(".dragDropped").children(idDrag).children().click(function() {
                    if($(this).parent().children().length <=1) {
                        $(this).parent().parent().parent().removeClass("state-highlight");
                    }
                    idProposal = ui.draggable.attr("id");
                    idProposal = idProposal.replace('draggable_', '');
                    if (idProposal) {
                        responses[idProposal] = 'NULL';
                    }
                    dragStop();
                    
                    $(this).parent().remove();
                    // reinitalisation of draggable
                    $(idDrag).draggable("enable");
                    $(idDrag).fadeTo(100, 1);
                });
                }
                //pour les images
                
                
                
                
                //pour les pas images
                
                
            },
        });
    });
});

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

function removeDrag() {
    
}