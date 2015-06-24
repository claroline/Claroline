var j; // For for instruction
var h; // For for instruction
var cur; // For the name of the cursor
var tempCoords = {}; // The coordonates of the answer zones
var validGraphic = $('#ValidGraphic'); // The form to validate
var size = $('#nbpointer').val(); // Number of pointers + 1

$(function () {

    for (j = 1 ; j < size ; j++) {

        // Make the answer zone draggable and save coordonates when stop drag
        $('#cursor' + j).draggable({
            containment : '#AnswerImg',
            cursor : 'move',

            stop: function(event, ui) {

                var stoppos = $(this).position();

                var marginTop = $('#AnswerImg').css("margin-top");

               // var x = stoppos.left; for center image
                var x =stoppos.left - $('#AnswerImg').position().left;
                var y = stoppos.top - $('#AnswerImg').position().top - parseInt(marginTop);

                tempCoords[event.target.id] = x + ' - ' + y;
            }
        });
    }
});

document.addEventListener('keydown', function (e) { // Reset all the pointers at initial place

    var space;

    if ($('#ContainerCursorPaper').length == 0) {
        space = 2;
    } else {
        space = 30;
    }

    if (e.keyCode == 67) { // Press c
        for (h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;

            $("#" + cur).css({
                "left" : String(h * space) + 'px',
                "top"  : '0px'
            });
        }

        tempCoords = {};
    }
}, false);

function NoEmptyAnswer() { // Verify before submit that student placed all answer zones

    for (h = 1 ; h < size ; h++) {
        cur = 'cursor' + h;

        // If answer zone not placed, put value 'a-a'
        if (!tempCoords[cur]) {
            tempCoords[cur] = 'a-a';
        }

        // Concatenate the answer zones informations to send it to the controller
        $('#answers').val($('#answers').val() + tempCoords[cur] + ';');
    }
    // Submit the form
    validGraphic.submit();
}
