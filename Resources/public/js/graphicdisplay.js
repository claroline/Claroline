var answerImg = document.getElementById('AnswerImg'); // The question image
var cible; // The selected pointer
var containerCursor = document.getElementById('ContainerCursor'); // Container to display the cursor
var cur; // Cursor with a defined number
var drag = false; // Allow or not to move the pointer
var ox = containerCursor.offsetLeft; // Top left of the cursor's container
var ref = document.getElementById('ref'); // The instructions div to get it position and place pointers after
var taille = document.getElementById('nbpoitner').value; // Number of pointers + 1
var validGraphic = document.getElementById('ValidGraphic'); // Form to end the test or look over answers
var x; // Mouse x position
var y; // Mouse y position

document.addEventListener('click', function (e) {
    "use strict";

    for (var j = 1 ; j < taille ; j++) {
        if (e.target.id == 'cursor' + j) {
            cible = e.target;
            document.body.style.cursor = 'pointer';
        }
    }
}, false);

document.addEventListener('click', function (e) {
    "use strict";
    if (drag === true) {

        var t1 = x - 10; // Position x of the mouse
        var t2 = y - 10; // Position y of the mouse
        var t3 = answerImg.offsetLeft + answerImg.width - 10; // Width of the image
        var t4 = answerImg.offsetTop + answerImg.height - 10; // Height of the image

        // Out of the image
        if ((t1) > (t3) || (t1) < (answerImg.offsetLeft - 7) || (t2) > (t4) || (t2) < (answerImg.offsetTop - 7)) {
            // Replace the cursor at its initial place
            cible.style.left = String(ox + (cible.id.substr(6)) * 25) + 'px';
            cible.style.top = String(ref.offsetTop + 70) + 'px';
        }

        cible = null;
        drag = false;
        document.body.style.cursor = 'default';
    }
}, false);

document.addEventListener('mousemove', function (e) {
    "use strict";
    if (cible) {
        // Position de la souris dans la fenetre :
        if (e.x != undefined && e.y != undefined) { // IE
            x = e.layerX;
            y = e.layerY;
        } else { // Firefox
            x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

       // Position de la souris dans l'image :
       //x -= canvas.offsetLeft;
       //y -= canvas.offsetTop;

        cible.style.left = String(x - 10) + 'px';
        cible.style.top = String(y - 10) + 'px';

        drag = true;
    }
}, false);

document.addEventListener('keydown', function (e) {
    "use strict";
    if (e.keyCode === 67) {
        for (var x = 1 ; x < taille ; x++) {
            cur = 'cursor' + x;
            document.getElementById(cur).style.left = String(ox + x * 25) + 'px';
            document.getElementById(cur).style.top = String(ref.offsetTop + 70) + 'px';
        }
    }
}, false);

window.addEventListener('load', function (e) {
    "use strict";
    for (var x = 1 ; x < taille ; x++) {
        cur = 'cursor' + x;
        document.getElementById(cur).style.left = String(ox + x * 25) + 'px';
        document.getElementById(cur).style.top = String(ref.offsetTop + 70) + 'px';
    }
}, false);

function EndTheTest(message) {
    "use strict";
    if (!confirm(message)) {
        return false;
    } else {
        validGraphic.action = '##';
        validGraphic.submit();
    }
}

function LookOver() {
    "use strict";
    validGraphic.action = '#';
    validGraphic.submit();
}