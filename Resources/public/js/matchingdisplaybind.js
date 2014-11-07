$(function() {
    
    var conn;

    jsPlumb.ready(function() {
        jsPlumb.setContainer($("body"));

        $(".origin").each(function() {
            $(this).draggable({
                cursor: 'move',
                revert: 'invalid',
                start: function(){
                    $(this).show();             
                },
            });
        });

        jsPlumb.makeSource($(".origin"), {
            anchor:"Right",
        });

        jsPlumb.makeTarget($(".droppable"), {
            anchor: "Left",
        });

        $(".droppable").each(function() {
            
            $(this).droppable({
                tolerance: "pointer",
                drop: function(event, ui) {
                    var drag = ui.draggable.attr("id");
                    var drop = $(this).attr("id");
                    conn = jsPlumb.connect({
                        source: drag,
                        target: drop,
                    });
                    jsPlumb.bind("click", function(conn, originalEvent) {
                        jsPlumb.detach(conn);
                    });
                }
            });
        });
    });
});