var responses = [];

$(function() {

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));

        //Create all draggable in source.
        jsPlumb.makeSource($(".origin"), {
            anchor: "Right",
            cssClass: "endPoints",
            isSource: true
        });

        //Create all droppable in target
         jsPlumb.makeTarget($(".droppable"), {
            anchor: "Left",
            cssClass: "endPoints",
            isTarget: true        
        });

        //defaults parameteres for all connections
        jsPlumb.importDefaults({
            ConnectionsDetachable:false,
            Connector: "Straight",
            DropOptions: {tolerance:"touch"},
            Endpoint: "Dot",
            EndpointStyle: {fillStyle:"#777", radius: 5},
            HoverPaintStyle: {strokeStyle:"red"},
            LogEnabled: false,
            PaintStyle: { strokeStyle:"#777", lineWidth: 4}
        });

        //if there are multiples same link
         jsPlumb.bind("beforeDrop", function(info){
            var connection = jsPlumb.getConnections({
                source:info["sourceId"],
                target:info["targetId"]
            });
            if(connection.length !== 0){
                //if the connection is already makes
                if (info["sourceId"] == connection[0].sourceId && info["targetId"] == connection[0].targetId) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        });

        //for remove connections
        jsPlumb.bind("click", function(connection) {
            var target = connection["target"]["id"];
            var connectionsTarget = jsPlumb.getConnections({
                target:target
            });
            if (connectionsTarget.length > 1) {
                jsPlumb.detach(connection);
            } else {
                jsPlumb.detach(connection);
                jsPlumb.removeAllEndpoints($("#" + target));
            }
        });
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