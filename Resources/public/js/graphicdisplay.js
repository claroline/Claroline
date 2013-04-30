var answerImg = document.getElementById('AnswerImg'); // The question image
var cible; // The selected pointer
var containerCursor = document.getElementById('ContainerCursor'); // Container to display the cursor
var cur; // Cursor with a defined number
var drag = false; // Allow or not to move the pointer
var out = false; // To know if is out the image
var ox = containerCursor.offsetLeft; // Top left of the cursor's container
var ref = document.getElementById('ref'); // The instructions div to get it position and place pointers after
var taille = document.getElementById('nbpointer').value; // Number of pointers + 1
var tempCoords = {}; // The coordonates of the answer zones
var validGraphic = document.getElementById('ValidGraphic'); // The form to validate
var x; // Mouse x position
var y; // Mouse y position
var j; // For for instruction
var longueur = ref.scrollHeight + 10; // height of the div "Instructions"

document.addEventListener('click', function (e) { // First click, get the selected answer zone
    //"use strict";
    for (j = 1 ; j < taille ; j++) {
        if (e.target.id == 'cursor' + j && drag == false) {
            cible = e.target;
            document.body.style.cursor = 'pointer';

            // If move answer zone, must delete old coordonates into the tab
            var w = cible.offsetLeft - answerImg.offsetLeft + 10;
            var c = cible.offsetTop - answerImg.offsetTop + 10;
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
    //"use strict";
    if (drag === true) {

        var t1 = x - 10; // Position x of the mouse
        var t2 = y - 10; // Position y of the mouse
        var t3 = answerImg.offsetLeft + answerImg.width - 10; // Width of the image
        var t4 = answerImg.offsetTop + answerImg.height - 10; // Height of the image

        // Out of the image
        if ((t1) > (t3) || (t1) < (answerImg.offsetLeft - 10) || (t2) > (t4) || (t2) < (answerImg.offsetTop - 10)) {
            // Replace the cursor at its initial place
            cible.style.left = String(ox + (cible.id.substr(6)) * 25) + 'px';
            cible.style.top = String(ref.offsetTop + longueur) + 'px';
            out = true;
        }

        if (out == false) {
            var contain = (cible.offsetLeft - answerImg.offsetLeft + 10) + '-' + (cible.offsetTop - answerImg.offsetTop + 10);
            tempCoords[cible.id] = contain;
        }

        cible = null;
        drag = false;
        out = false;
        document.body.style.cursor = 'default';
    }
}, false);

document.addEventListener('mousemove', function (e) {
    //"use strict";
    if (cible) {
        // Position de la souris dans la fenetre :
        if (e.x != undefined && e.y != undefined) { // IE
            x = e.layerX;
            y = e.layerY;
        } else { // Firefox
            x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        cible.style.left = String(x - 10) + 'px';
        cible.style.top = String(y - 10) + 'px';

        drag = true;
    }
}, false);

document.addEventListener('keydown', function (e) { // Reset all the pointers
    //"use strict";
    if (e.keyCode === 67) {
        for (var x = 1 ; x < taille ; x++) {
            cur = 'cursor' + x;
            document.getElementById(cur).style.left = String(ox + x * 25) + 'px';
            document.getElementById(cur).style.top = String(ref.offsetTop + longueur) + 'px';
        }

        tempCoords = {};
    }
}, false);

function NoEmptyAnswer() { // Verify before submit that student placed all answer zones

    for (var x = 1 ; x < taille ; x++) {

        var cur = 'cursor' + x;

        if (!tempCoords[cur]) {
            tempCoords[cur] = 'a-a';
        }
        document.getElementById('answers').value += tempCoords[cur] + ';';
    }
    validGraphic.submit();
}