var responses = [];
var balises = [];

$(function() {

    if($(".draggable").width() > $(".droppable").width()) {
        var $widthDraggable = $(".draggable").width();
        var $widthDroppable = $widthDraggable;
        $(".droppable").width($widthDroppable * 1.5);
    }

    $(".draggable").each(function() {
        creationDraggable($(this));
    });

    $(".droppable").each(function() {
        if($(this).children().length > 2) {
            childrens = $(this).children().length;
            var i=2;
            //deplacement of li in ul
            for(i=2; i<childrens; i++) {
                $(this).children(".dragDropped").prepend($(this).children().last().clone());
                $(this).children().last().remove();
            }
            $(this).addClass("state-highlight");
            $(this).children(".dragDropped").children().each(function() {
                id = $(this).attr('id');
                numberId = id.replace('draggable_', '');
                balises[numberId] = $(this);
                idDrag = $(this).attr('id');
                $(this).append("<a id=reset"+idDrag+"><img src="+deleteImage()+" /></a>");
            });
        }
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
                disableDrag(idDrag, $(this));
            },
        });
    });

    $(".origin").each(function() {
        if($(this).children().children().length == 0) {
            id = $(this).attr('id');
            numberId = id.replace('div_', '');
            $(this).children().append(balises[numberId].clone());
            $(this).children().children().children("a").remove();
            $(this).children().children().removeClass();
            $(this).children().children().addClass("draggable ui-draggable ui-draggable-disabled ui-state-disabled");
            idDrag = id.replace('div', 'draggable');
            idDrag = "#"+idDrag;
            // discolor the text
            $(idDrag).fadeTo(100, 0.3);
            droppable = balises[numberId].parent().parent();
            creationDraggable($(this).children().children());
            $(idDrag).draggable("enable");
            disableDrag(idDrag, droppable);
        }
        $(this).droppable({
            tolerance: "pointer",
        });
    });
});

function creationDraggable(draggable){
    draggable.draggable({
        cursor: 'move',
        revert: 'invalid',
        helper: 'clone',
        stop: function(event, ui) {
//dump(responses);
            dragStop();
        },
    });
}

function disableDrag(idDrag, parent){
    var draggableDropped = parent.children(".dragDropped").children(idDrag);
        parent.children(".dragDropped").children(idDrag).children().last().click(function() {
            if(parent.children(".dragDropped").children().length <= 1) {
                parent.removeClass("state-highlight");
            }
            removeDrag(idDrag, draggableDropped);
        });
}

function removeDrag(idDrag, draggableDropped) {
    idProposal = idDrag;
    idProposal = idProposal.replace('#draggable_', '');
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