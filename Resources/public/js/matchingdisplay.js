var responses = [];

$(function() {
    $(".draggable").each(function() {
        $(this).draggable({
            cursor: 'move',
            revert: 'invalid',
            stop: function() {
//dump(responses);
                dragStop();
            },
        });
    });
    $(".origin").droppable();

    $(".droppable").each(function() {
        $(this).droppable({
            tolerance: "pointer",
            activeClass: "ui-state-hover",
            hoverClass: "ui-state-active",
            drop: function(event, ui) {
                $(this).addClass("ui-state-highlight");
                idLabel = $(this).attr('id');
                idLabel = idLabel.replace('droppable_', '');
                idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                if (idProposal) {
                    responses[idProposal] = idLabel;
                }
            },
            out: function(event, ui) {
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
