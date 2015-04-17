$(function (){
    
    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));


alert('coucou');
        //Create all draggable in source
        jsPlumb.makeSource($(".origin"), {
            isSource: true,
            anchor: "Right",
            cssClass: "endPoints",
        });

        //Create all droppable in target
        jsPlumb.makeTarget($(".droppable"), {
            isTarget: true,
            anchor: "Left",
            cssClass: "endPoints",
        });
        
        //defaults parameteres for all connections
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
    });
});
