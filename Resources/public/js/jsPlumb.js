function source() {
    jsPlumb.makeSource($(".origin"), {
        isSource: true,
        anchor: "Right",
        cssClass: "endPoints",
    });
}

function target() {
    jsPlumb.makeTarget($(".droppable"), {
        isTarget: true,
        anchor: "Left",
        cssClass: "endPoints",
    });
}

function defaultParameters() {
    jsPlumb.importDefaults({
        ConnectionsDetachable:false,
        Connector: "Straight",
        Endpoint: "Dot",
        EndpointStyle: {fillStyle:"#777", radius: 5},
        PaintStyle: { strokeStyle:"#777", lineWidth: 4},
        HoverPaintStyle: {strokeStyle:"red"},
        LogEnabled: false,
        DropOptions: {tolerance:"touch"},
    });
}

function multiplesLinks() {
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
}

function removeConnections() {
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
}