var responses = [];

$(function() {

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));
        
        // for create source element for jsPlumb
        $(".origin").each(function() {
            jsPlumb.addEndpoint(this, {
                anchor: 'RightMiddle',
                cssClass: "endPoints",
                isSource: true,
                maxConnections: -1
            });
        });

         // for create target element for jsPlumb
        $(".droppable").each(function() {
            jsPlumb.addEndpoint(this, {
                anchor: 'LeftMiddle',
                cssClass: "endPoints",
                isTarget: true,
                maxConnections: -1
            });
        });

        // for reset all connection
        $("#resetAll").click(function() {
            jsPlumb.detachEveryConnection();
        });

        // defaults parameteres for all connections
        jsPlumb.importDefaults({
            Anchors: ["RightMiddle", "LeftMiddle"],
            ConnectionsDetachable: false,
            Connector: "Straight",
            DropOptions: {tolerance: "touch"},
            HoverPaintStyle: {strokeStyle: "red"},
            LogEnabled: true,
            PaintStyle: {strokeStyle: "#777", lineWidth: 4}
        });

        // if there are multiples same link
        jsPlumb.bind("beforeDrop", function(info) {
            var connection = jsPlumb.getConnections({
                source: info["sourceId"],
                target: info["targetId"]
            });
            if (connection.length !== 0) {
                // if the connection is already makes
                if (info["sourceId"] == connection[0].sourceId && info["targetId"] == connection[0].targetId) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        });

        // for remove connections
        jsPlumb.bind("click", function(connection) {
            jsPlumb.detach(connection);
        });
    });

    // for check in connections
    var formBalise = $("body").find("form");
    var idFormBalise = formBalise.attr("id");
    // validate in exercice
    if (idFormBalise == "formResponse") {
        $("#" + idFormBalise).submit(function() {
            $(".origin").each(function() {
                checkIn($(this));
            });
        });
        // validate in test in banque question
    } else {
        $("#submit_response").click(function() {
            $(".origin").each(function() {
                checkIn($(this));
            });
        });
    }
});

function placeProposal(idLabel, idProposal) {
    // for exercice, if go on previous question, replace connections
    $(function() {
        jsPlumb.ready(function() {
            jsPlumb.connect({
                source: 'draggable_' + idProposal,
                target: 'droppable_' + idLabel
            });
        });
    });
}

// registration of relations
function checkIn(divProposal) {
    var idProposal = divProposal.attr("id");
    var proposal = idProposal.replace('draggable_', '');
    var connections = jsPlumb.getConnections({source: idProposal});
    if (connections[0]) {
        responses[proposal] = 'NULL';
        responses[proposal] = proposals = [];
        for (i = 0; i < connections.length; i++) {
            var idLabel = connections[i].targetId;
            var label = idLabel.replace('droppable_', '');
            responses[proposal][i] = label;
        }
        dragStop();
    }
}

function dragStop() {
    // registration of responses in the html balise
    var resp = '';
    $.each(responses, function(key, value) {
        if (value) {
            resp = resp + key + ',' + value + ';';
        }
    });
    $('#jsonResponse').val(resp);
}
