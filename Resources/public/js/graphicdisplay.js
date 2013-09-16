var j; // For for instruction
var h; // For for instruction
var cur; // For the name of the cursor
var tempCoords = {}; // The coordonates of the answer zones
var length = 2; // margin which separate the cursor and the instructions
var validGraphic = $('#ValidGraphic'); // The form to validate
var ox = $('#ContainerCursor').position().left; // The start position to display the cursor inline
var size = $('#nbpointer').val(); // Number of pointers + 1

window.onload = function () {
    // Display the cursor to answer
    // If test as paper
    if ($('#PositionInit').val() == 'true') {
        for (h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;

            $("#" + cur).css({
                "left" : String(ox - 20 + h * 37) + 'px',
                "top"  : String(length) + 'px'
            });
        }
    // Or test only the question
    } else {
        for (h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;

            if ($("#" + cur).css("top") == '0px') {
                $("#" + cur).css({
                    "left" : String(ox - 20 + h * 37) + 'px',
                    "top"  : String(length) + 'px'
                });
            }
        }
    }
};

$(function () {

    var marginTop = $('#AnswerImg').css("margin-top");

    for (j = 1 ; j < size ; j++) {

        // Make the answer zone draggable and save coordonates when stop drag
        $('#cursor' + j).draggable({
            containment : '#AnswerImg',
            cursor : 'move',

            stop: function(event, ui) {

                var stoppos = $(this).position();

                tempCoords[event.target.id] = (stoppos.left + 10) + ' - ' + ((stoppos.top - (marginTop.substr(0, marginTop.indexOf('p')))) + 10);
            }
        });
    }
});

document.addEventListener('keydown', function (e) { // Reset all the pointers at initial place

    if (e.keyCode == 67) { // Press c
        for (h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;

            $("#" + cur).css({
                "left" : String(ox - 20 + h * 37) + 'px',
                "top"  : String(length) + 'px'
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