var answerImg = document.getElementById('AnswerImg'); // The question image
var cible; // The selected pointer
var containerCursor = document.getElementById('ContainerCursor'); // Container to display the cursor
var cur; // Cursor with a defined number
var drag = false; // Allow or not to move the pointer
var out = false; // To know if is out the image
var ox = containerCursor.offsetLeft; // Top left of the cursor's container
var ref = document.getElementById('ref'); // The instructions div to get it position and place pointers after
var taille = document.getElementById('nbpointer').value; // Number of pointers + 1
var tempCoords = {};
var validGraphic = document.getElementById('ValidGraphic'); // The form to validate
var x; // Mouse x position
var y; // Mouse y position
var ze;
var j;

document.addEventListener('click', function (e) {
    //"use strict";

    for (j = 1 ; j < taille ; j++) {
        if (e.target.id == 'cursor' + j) {
            cible = e.target;
            document.body.style.cursor = 'pointer';

            var w = cible.offsetLeft - answerImg.offsetLeft +7;
            var c = cible.offsetTop - answerImg.offsetTop+7;
            var temp = w + '-' + c;

//            for (ze = 0 ; ze < taille-1 ; ze++) {
//                if(temp == tempCoords[ze]){
//                    tempCoords.splice(ze, 1);
//                }
//            }

           for (var ci in tempCoords) {
               if (temp == tempCoords[ci] && ci == e.target.id && drag == false){
                   delete tempCoords[ci];
               }
            }
        }
    }
}, false);


document.addEventListener('click', function (e) {
    //"use strict";
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
            out = true;
        }
        
        if(out == false){
            var contain = (cible.offsetLeft - answerImg.offsetLeft +7) + '-' + (cible.offsetTop - answerImg.offsetTop+7);
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

       // Position de la souris dans l'image :
       //x -= cible.offsetLeft;
       //y -= cible.offsetTop;

        cible.style.left = String(x - 10) + 'px';
        cible.style.top = String(y - 10) + 'px';

        drag = true;
    }
}, false);

document.addEventListener('keydown', function (e) {
    //"use strict";
    if (e.keyCode === 67) {
        for (var x = 1 ; x < taille ; x++) {
            cur = 'cursor' + x;
            document.getElementById(cur).style.left = String(ox + x * 25) + 'px';
            document.getElementById(cur).style.top = String(ref.offsetTop + 70) + 'px';
        }
        
        tempCoords = {};
    }
}, false);

window.addEventListener('load', function (e) {
    //"use strict";
    for (var x = 1 ; x < taille ; x++) {
        cur = 'cursor' + x;
        document.getElementById(cur).style.left = String(ox + x * 25) + 'px';
        document.getElementById(cur).style.top = String(ref.offsetTop + 70) + 'px';
    }
}, false);

function NoEmptyAnswer(noAnswerZone) { 
    
    var item = getTaille(tempCoords);
    
    if (item == 0) { 
        alert(noAnswerZone);
        return false;
    } else {
        for (var cur in tempCoords) {
            document.getElementById('answers').value += tempCoords[cur]+',';
        }
        validGraphic.submit();
    }
}

function getTaille(tab){
    
    var i = 0;
    
    for (var id in tab) {
        i++;
    }
    return i;
}