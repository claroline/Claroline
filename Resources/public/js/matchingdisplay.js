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