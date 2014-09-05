$(function () {

    $(".draggable").each(function () {
        $(this).draggable({
        cursor : 'move',
        revert: 'invalid',
    });
    });
    
    $(".origin").each(function () {
        $(this).droppable();
    });
        
    $(".droppable").each(function () {
        $(this).droppable({
            activeClass: "ui-state-hover",
            hoverClass: "ui-state-active",
            drop: function( event, ui ) {
                $( this )
                    .addClass( "ui-state-highlight" );
            },
            
//            applyDropEvents: function() {
//                alert("coucou");
//            }
        });
    });
    
    
//    $(".droppable").click(function(){
//        $(this).droppable({
//            activeClass: "test",
//            alert("coucou");
//        });
//    });
    
    
    
});