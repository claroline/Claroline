var responses = [];

$(function() {

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));

        //Create all draggable in source
        jsPlumb.makeSource($(".origin"), {
            isSource: true,
            anchor: "Right",
            cssClass: "endPoints",
//            endpointStyle: {hoverClass: "test"},
        });

        //Create all droppable in target
        jsPlumb.makeTarget($(".droppable"), {
            isTarget: true,
            anchor: "Left",
            cssClass: "endPoints",
//            endpointStyle: {hoverClass: "test"},
        });
        
        //defaults parameteres for all connections
        jsPlumb.importDefaults({
            ConnectionsDetachable:false,
            Connector: "Straight",
            Endpoint: "Dot",
            EndpointStyle: {fillStyle:"#777", radius: 5},
            PaintStyle: { strokeStyle:"#777", lineWidth: 20},//4
            HoverPaintStyle: {strokeStyle:"red"},
            LogEnabled: false,
            DropOptions: {tolerance:"touch"},
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
                target:target,
            });
            if (connectionsTarget.length > 1) {
                jsPlumb.detach(connection);
            } else {
                jsPlumb.detach(connection);
                jsPlumb.removeAllEndpoints($("#" + target));
            }
        });
    });

    $("#droppable_34").click(function() {
        $(".origin").each(function() {
            checkIn($(this));
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
    for(i = 0; i < connections.length; i++) {
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
            resp = resp + key + ',' + value + ';';
        }
    });
    $('#jsonResponse').val(resp);
}