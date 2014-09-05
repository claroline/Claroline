var responses = [];
    
$(function () {

    $(".draggable").each(function () {
        $(this).draggable({
            cursor : 'move',
            revert: 'invalid',
            stop: function() {
                //dump(responses);
            },
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
                $( this ).addClass( "ui-state-highlight" );
                idLabel = $( this ).attr('id');
                idLabel = idLabel.replace('droppable_','');
                
                idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                if (idProposal) {
                    responses[idProposal] = idLabel;
                }
                
            },
            out: function( event, ui ) {
                idProposal = ui.draggable.attr("id");
                idProposal = idProposal.replace('draggable_', '');
                if (idProposal) {
                    responses[idProposal] = 'NULL';
                }
                
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
    
function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    alert(out);

    // or, if you wanted to avoid alerts...

//    var pre = document.createElement('pre');
//    pre.innerHTML = out;
//    document.body.appendChild(pre)
}   
    
});

$('#submit_response').click( function () {
    var resp = '';
//    jsonResponse = JSON.stringify(responses);
//    $('#jsonResponse').val(jsonResponse);
//    alert(jsonResponse);

    $.each( responses, function( key, value ) {
        if (value) {
            resp = resp + key + '-' + value + ';';
        }
    });
    $('#jsonResponse').val(resp);
});