var responses = [];
var balisesLiDropped = [];

$(function() {

    if($(".draggable").width() > $(".droppable").width()) {
        var $widthDraggable = $(".draggable").width();
        var $widthDroppable = $widthDraggable;
        $(".droppable").width($widthDroppable * 1.5);
    }

    $(".draggable").each(function() {
        activeDraggable($(this));
    });

    $(".droppable").each(function() {

        //for exercice, if go on previous question
        if($(this).children().length > 2) {
            childrens = $(this).children().length;
            var i=2;

            //displacement of each li in ul
            for(i=2; i<childrens; i++) {
                $(this).children(".dragDropped").prepend($(this).children().last().clone());
                $(this).children().last().remove();
            }

            //active the css class when drag dropped
            $(this).addClass("state-highlight");
            $(this).children(".dragDropped").children().each(function() {

                //add the image for delete drag
                id = $(this).attr('id');
                idNumber = id.replace('draggable_', '');
                balisesLiDropped[idNumber] = $(this);
                idDrag = $(this).attr('id');
                $(this).append("<a class='fa fa-trash' id=reset"+idDrag+"></a>");
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

                //clone the drag in drop
                $(this).children(".dragDropped").append($(ui.helper).clone().removeClass("draggable ui-draggable ui-draggable-dragging")
                        .removeAttr('style').css("list-style-type","none").addClass(idProposal));

                $("."+idProposal).attr('id', idProposal);
                var idDrag = "#"+idProposal;
                $(this).find(".dragDropped").children(idDrag).append("<a class='fa fa-trash' id=reset"+idDrag+"></a>");

                //desactivate the drag like he is dropped
                $(idDrag).draggable("disable");

                // discolor the text
                $(idDrag).fadeTo(100, 0.3);
                disableDrag(idDrag, $(this));
            },
        });
    });

    $(".origin").each(function() {
        //for exercice, if go on previous question
        if($(this).children().children().length == 0) {
            id = $(this).attr('id');
            idNumber = id.replace('div_', '');

            //clones the li balise of draggable and takes the correct appearance like if is dropped
            $(this).children().append(balisesLiDropped[idNumber].clone());
            $(this).children().children().children("a").remove();
            $(this).children().children().removeClass();
            $(this).children().children().addClass("draggable ui-draggable ui-draggable-disabled ui-state-disabled");
            idDrag = id.replace('div', 'draggable');
            idDrag = "#"+idDrag;
            $(idDrag).fadeTo(100, 0.3);
            droppable = balisesLiDropped[idNumber].parent().parent();
            activeDraggable($(this).children().children());
            $(idDrag).draggable("enable");
            disableDrag(idDrag, droppable);
        }

        $(this).droppable({
            tolerance: "pointer",
        });
    });
});

function activeDraggable(draggable) {
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

function disableDrag(idDrag, parent) {
    var draggableDropped = parent.children(".dragDropped").children(idDrag);

    //removes a drag dropped
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
    dragStop();
}

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }
    alert(out);
}