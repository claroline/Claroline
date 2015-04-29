var responses = [];

$(function() {

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));

        //Create all draggable in source.
        source();

        //Create all droppable in target
        target();

        //defaults parameteres for all connections
        defaultParameters();

        //if there are multiples same link
        multiplesLinks();

        //for remove connections
        removeConnections();
    });

    //for check in connections
    var formBalise = $("body").find("form");
    var idFormBalise = formBalise.attr("id");
    if(idFormBalise == "formResponse") {
        $("#"+idFormBalise).submit(function() {
            $(".origin").each(function() {
                checkIn($(this));
            });
        });
    } else {
        $("#submit_response").click(function() {
            $(".origin").each(function() {
                checkIn($(this));
            });
        });
    }
});

function placeProposal(idLabel, idProposal) {
    //for exercice, if go on previous question, replace connections
    $(function() {
        jsPlumb.ready(function() {
            jsPlumb.connect({
                source: 'draggable_' + idProposal,
                target: 'droppable_' + idLabel
            });
        });
    });
}

//registration of relations
function checkIn(divProposal) {
    var idProposal = divProposal.attr("id");
    var proposal = idProposal.replace('draggable_', '');
    var connections = jsPlumb.getConnections({source:idProposal});
    if(connections[0]) {
        responses[proposal] = 'NULL';
        responses[proposal] = proposals = [];
        for(i = 0; i < connections.length; i++) {
            var idLabel = connections[i].targetId;
            var label = idLabel.replace('droppable_', '');
            responses[proposal][i] = label;
        }
        dragStop();
    }
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