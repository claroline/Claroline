var responses = [];

$(function() {

    if($(".draggable").width() > $(".droppable").width()) {
        var $widthDraggable = $(".draggable").width();
        var $widthDroppable = $widthDraggable;
        $(".droppable").width($widthDroppable * 1.5);
    }

    $(".draggable").each(function() {
//        var $test = $(this).text().length;
//        if($(this).width() > $(this).text().length) {
//
//            $(this).width($test);
//        }

        $(this).draggable({
            cursor: 'move',
            revert: 'invalid',
            stop: function() {
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
        var $countDraggable = 0;
        $(this).droppable({
            tolerance: "pointer",
            activeClass: "state-hover",
            hoverClass: "state-active",
            drop: function(event, ui) {
                $countDraggable = $countDraggable + 1;
                $(this).addClass("state-highlight");
                idLabel = $(this).attr('id');
                idLabel = idLabel.replace('droppable_', '');
                idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                if (idProposal) {
                    responses[idProposal] = idLabel;
                }
//                if(ui.draggable.width() > $(this).width()) {
//                    $(this).width(ui.draggable.width() * 1.5);
//                }
                if(ui.draggable.height() > $(this).height()) {
                    $(this).height(ui.draggable.height() * 1.5);
                }
            },
            out: function(event, ui) {
                $countDraggable = $countDraggable - 1;
                if ($countDraggable < 0) {
                    $countDraggable = 0;
                }
                if($countDraggable == 0 ) {
                    $(this).removeClass("state-highlight");
                }
                idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                if (idProposal) {
                    responses[idProposal] = 'NULL';
                }
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