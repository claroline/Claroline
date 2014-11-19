var responses = [];

$(function() {

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));

        //Create all draggable in source
        jsPlumb.makeSource($(".origin"), {
            isSource:true,
            anchor: "Right",
        });

        //Create all droppable in target
        jsPlumb.makeTarget($(".droppable"), {
            isTarget:true,
            anchor: "Left",
        });

        //defaults parameteres for all connections
        jsPlumb.importDefaults({
            ConnectionsDetachable:false,
            Connector : "Straight",
            Endpoint : "Dot",
            EndpointStyle : {fillStyle:"#777", radius: 5},
            PaintStyle : { strokeStyle:"#777", lineWidth: 4},
            HoverPaintStyle:{strokeStyle:"red"},
            LogEnabled:false,
            DropOptions:{tolerance:"touch"},
            onMaxConnections: 1,
        });

        jsPlumb.bind("click", function(connections) {
            jsPlumb.detach(connections);
        });
    });

    //for check in connections
    var formBalise = $("body").find("form");
    var idFormBalise = formBalise.attr("id");
    if(idFormBalise == "formResponse") {
        $("#formResponse").submit(function() {
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
    responses[idProposal] = idLabel;
    dragStop();
}

//registration of relations
function checkIn(divProposal) {
        var idProposal = divProposal.attr("id");
        var proposal = idProposal.replace('draggable_', '');
        responses[proposal] = 'NULL';
        var connections = jsPlumb.getConnections({source:idProposal});
        responses[proposal] = proposals = [];
        for(i=0; i<connections.length; i++) {
            var idLabel = connections[i].targetId;
            var label = idLabel.replace('droppable_', '');
            responses[proposal][i] = label;
        }
        dragStop();
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