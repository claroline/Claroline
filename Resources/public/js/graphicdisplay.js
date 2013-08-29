var answerImg = document.getElementById('AnswerImg'); // The question image
var target; // The selected pointer
var containerCursor = document.getElementById('ContainerCursor'); // Container to display the cursor
var cur; // Cursor with a defined number
var drag = false; // Allow or not to move the pointer
var out = false; // To know if is out the image
var ox = containerCursor.offsetLeft; // Top left of the cursor's container
var size = document.getElementById('nbpointer').value; // Number of pointers + 1
var tempCoords = {}; // The coordonates of the answer zones
var validGraphic = document.getElementById('ValidGraphic'); // The form to validate
var x; // Mouse x position
var y; // Mouse y position
var j; // For for instruction
var length = 2;

window.onload = function () {

    if (document.getElementById('PositionInit').value == 'true') {
        for (var h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;
            document.getElementById(cur).style.left = String(ox - 20 + h * 37) + 'px';
            document.getElementById(cur).style.top = String(length) + 'px';
        }
    } else {
        for (var h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;
            if (document.getElementById(cur).style.left == '') {
                document.getElementById(cur).style.left = String(ox - 20 + h * 37) + 'px';
                document.getElementById(cur).style.top = String(length) + 'px';
            }
        }
    }
};

document.addEventListener('click', function (e) { // First click, get the selected answer zone

    for (j = 1 ; j < size ; j++) {
        if (e.target.id == 'cursor' + j && drag == false) {
            target = e.target;
            document.body.style.cursor = 'pointer';

            // If move answer zone, must delete old coordonates into the tab
            var w = target.offsetLeft - answerImg.offsetLeft + 10;
            var c = target.offsetTop - answerImg.offsetTop + 10;
            var temp = w + '-' + c;

            for (var ci in tempCoords) {
                if (temp == tempCoords[ci] && ci == e.target.id && drag == false) {
                    delete tempCoords[ci];
                }
            }
        }
    }
}, false);

document.addEventListener('click', function (e) { // Second click, place the selected answer zone

    if (drag == true) {

        var t1 = x - 10; // Position x of the mouse
        var t2 = y - 10; // Position y of the mouse
        var t3 = answerImg.offsetLeft + answerImg.width - 10; // Width of the image
        var t4 = answerImg.offsetTop + answerImg.height - 10; // Height of the image

        // Out of the image
        if ((t1) > (t3) || (t1) < (answerImg.offsetLeft - 10) || (t2) > (t4) || (t2) < (answerImg.offsetTop - 10)) {
            // Replace the cursor at its initial place
            target.style.left = String(ox - 20 + (target.id.substr(6)) * 37) + 'px';
            target.style.top = String(length) + 'px';
            out = true;
        }

        if (out == false) {
            var contain = (target.offsetLeft - answerImg.offsetLeft + 10) + '-' + (target.offsetTop - answerImg.offsetTop + 10);
            tempCoords[target.id] = contain;
        }

        target = null;
        drag = false;
        out = false;
        document.body.style.cursor = 'default';
    }
}, false);

document.addEventListener('mousemove', function (e) {

    if (target) {
        // Position of the mouse into the window
        getMousePosition(e);

        var margin = parseInt(answerImg.style.marginTop.substr(0, answerImg.style.marginTop.indexOf('p')));

        x -= document.getElementsByClassName('col-md-9 section-content')[0].offsetLeft + document.getElementById('Answer').offsetLeft;
        y -= document.getElementById('Answer').offsetTop + answerImg.offsetTop + margin;

        target.style.left = String(x - 10) + 'px';
        target.style.top = String(y - 20) + 'px';

        drag = true;
    }
}, false);

document.addEventListener('keydown', function (e) { // Reset all the pointers

    if (e.keyCode == 67) { // Press c
        for (var h = 1 ; h < size ; h++) {
            cur = 'cursor' + h;
            document.getElementById(cur).style.left = String(ox - 20 + h * 37) + 'px';
            document.getElementById(cur).style.top = String(length) + 'px';
        }

        tempCoords = {};
    }
}, false);

function NoEmptyAnswer() { // Verify before submit that student placed all answer zones

    for (var h = 1 ; h < size ; h++) {

        var cur = 'cursor' + h;

        // If answer zone not placed, default value
        if (!tempCoords[cur]) {
            tempCoords[cur] = 'a-a';
        }
        // Concatenate the answer zones informations to send it to the controller
        document.getElementById('answers').value += tempCoords[cur] + ';';
    }
    // Submit the form
    validGraphic.submit();
}

function getMousePosition(e) {
    var position = new Array();

    if (e.x != undefined) {
        x = e.pageX;
        y = e.pageY;
    } else if (e) {
        x = e.clientX;
        y = e.clientY;
    } else {
        var monBody = document.documentElement || document.body;
        x = window.event.clientX + monBody.scrollLeft;
        y = window.event.clientY + monBody.scrollTop;
    }

    position[0] = x;
    position[1] = y;

    return position;
}