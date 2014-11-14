var responses = [];
$(function() {

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));
        
        jsPlumb.makeSource($(".origin"), {
            isSource:true,
            anchor: "Right",
        });

        jsPlumb.makeTarget($(".droppable"), {
            isTarget:true,
            anchor: "Left",
        });
        
        jsPlumb.importDefaults({
            ConnectionsDetachable:false,
            Connector : "Straight",
            Endpoint : "Dot",
            EndpointStyle : {fillStyle:"#777", radius: 5},
            PaintStyle : { strokeStyle:"#777", lineWidth: 4},
            HoverPaintStyle:{strokeStyle:"red"},
            LogEnabled:false,
            DropOptions:{tolerance:"touch"},
            onMaxConnections: 1
        });
                    
        jsPlumb.bind("click", function(connections){
            jsPlumb.detach(connections);
        });
        
        $("#droppable_48").click(function() {
            $(".origin").each(function() {
                var idProposal = $(this).attr("id");
                var proposal = idProposal.replace('origin_', '');
                responses[proposal] = 'NULL';
                var connections = jsPlumb.getConnections({source:idProposal});
                responses[proposal] = proposals = [];
                for(i=0; i<connections.length; i++) {
                    var idLabel = connections[i].targetId;
                    var label = idLabel.replace('droppable_', '');
                    responses[proposal][i] = label;
                }
                dragStop();
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
    
    
});