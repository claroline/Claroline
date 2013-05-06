// :::::::::::::::::::::::::::::::::::::::::: Declaration variables :::::::::::::::::::::::::::::::::::::::::::::::::::
var allow = false; // To know if answer zone is selected before resize
var answerImg = document.getElementById('AnswerImage'); // The background answer image
var cible; // The selected answer zones
var el = document.getElementById('movable'); // To get the shape and the color of the answer zone
var imgx; // Coord x of the answer zone
var imgy; // Coord y of the answer zone
var indice = 0; // Number of answer zone
var j; // For for instruction
var mousex; // Position x of the mouse
var mousey; // position y of the mouse
var moving = false; // If move answer zones
var score = {}; // The score of coords
var pressMAJ; // If key MAJ pressed or not
var pressCTRL; // If key CTRL pressed or not
var pressALT; // If key ALT pressed or not
var pressS; // If key s pressed or not
var resizing = false; // If resize answer zone
var result; // src of the answer image
var sens; // Move of the mouse
var scalex = 0; // Width of the image after resize
var scaley = 0; // Height of the image after resize
var value = 0; // Size of the resizing
var x = 0; // Mouse x after move
var xPrecedent = 0; // Mouse x before move
var y = 0; // Mouse y after move
var yPrecedent = 0; // Mouse y before move


var newx;
var newy;


// Display alert into navigator language
if (navigator.browserLanguage) {
    var language = navigator.browserLanguage; // IE
} else {
    var language = navigator.language; // FIrefox
}

// Instructions aren't displayed (by default) for more visibility
document.getElementById('Instructions').style.display = 'none';
document.getElementById('Consignes').style.display = 'block';

// :::::::::::::::::::::::::::::::::::::::::: Functions :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

function DisplayInstruction() { // If click, instructions are displayed
    document.getElementById('Instructions').style.display = 'block';
    document.getElementById('Consignes').style.display = 'none';
}

// Get the url's picture matching to the label in the list
function sendData(select, path, prefx) {
    //"use strict";

    // Send the label of the picture to get the adress in order to display it
    $.ajax({
        type: 'POST',
        url: path,
        data: {
            value : select,
            prefix : prefx
        },
        cache: false,
        success: function (data) {
            answerImg.src = data;
        }
    });
}

// Display the selected picture
function LoadPic(path, prefx) {
    //"use strict";

    var list = document.InterGraphForm.ujm_exobundle_interactiongraphictype_document; // List of all the user's pictures
    var select = list.options[list.selectedIndex].innerHTML; // Label of the selected picture

    sendData(select, path, prefx);

    // New picture load, initialization var and remove previous answer zones :
    value = 0;

    for (j = 0 ; j < indice ; j++) {
        if (document.getElementById('img' + j)) {
            document.getElementById('img' + j).parentNode.removeChild(document.getElementById('img' + j));
        }
    }
    indice = 0;
    point = {};
}

// Submit form without an empty field
function Verifier(noTitle, noQuestion, noImg, noAnswerZone) {
    //"use strict";

    var imgOk = false; // Image is upload
    var questionOk = false; // Question is asked
    var titleOk = false; // Question has a title
    var zoneOk = false; // Answer zones are defined
    var empty = false;

    for (j = 0 ; j < indice ; j++) {

        var nom = 'img' + j;
        var choix = document.getElementById(nom);

        // If at least one answer zone exist
        if (choix) {
            empty = true;
            break;
        }
    }

//     No title
    if (document.InterGraphForm.ujm_exobundle_interactiongraphictype_interaction_question_title.value === '') {
        alert(noTitle);
        return false;
    } else {
        titleOk = true;
    }

    // No question asked
    if (document.InterGraphForm.ujm_exobundle_interactiongraphictype_interaction_invite.value === '' && titleOk === true) {
        alert(noQuestion);
        return false;
    } else {
        questionOk = true;
    }

    // No picture load
    if (document.getElementById('AnswerImage').src.indexOf('users_document') == -1 && titleOk === true && questionOk === true) {
        alert(noImg);
        return false;
    } else {
        imgOk = true;
    }

    // No answer zone
    if (empty == false && imgOk === true && titleOk === true && questionOk === true) {
        alert(noAnswerZone);
        return false;
    } else {
        zoneOk = true;
    }

    // Submit if required fields not empty
    if (imgOk === true && zoneOk === true && titleOk === true && questionOk === true) {
        document.getElementById('imgwidth').value = answerImg.width; // Pass width of the image to the controller
        document.getElementById('imgheight').value = answerImg.height; // Pass height of the image to the controller

        for (j = 0 ; j < indice ; j++) {

            var imgN = 'img' + j;
            var selectedZone = document.getElementById(imgN);

            if (selectedZone) {

                imgx = parseInt(selectedZone.style.left.substr(0, selectedZone.style.left.indexOf('p'))) +
                    (selectedZone.width / 2);
                imgx -= answerImg.offsetLeft; // Position x answer zone

                imgy = parseInt(selectedZone.style.top.substr(0, selectedZone.style.top.indexOf('p'))) +
                    (selectedZone.height / 2);
                imgy -= answerImg.offsetTop; // Position y answer zone

                // Concatenate informations of the answer zones 
                var val = selectedZone.src + ';' + imgx + '_' + imgy + '-' + point[imgN] + '~' + selectedZone.width;

                // And send it to the controller
                document.getElementById('coordsZone').value += val + ',';
            }
        }

        document.getElementById('InterGraphForm').submit();
    }
}

// Change the shape and the color of the answer zone
function changezone(prefix) {
    //"use strict";

    if (document.getElementById('shape').value === 'circle') {
        switch (document.getElementById('color').value) {
        case 'white' :
            el.src = prefix + 'circlew.png';
            break;

        case 'red' :
            el.src = prefix + 'circler.png';
            break;

        case 'blue' :
            el.src = prefix + 'circleb.png';
            break;

        case 'purple' :
            el.src = prefix + 'circlep.png';
            break;

        case 'green' :
            el.src = prefix + 'circleg.png';
            break;

        case 'orange' :
            el.src = prefix + 'circleo.png';
            break;

        case 'yellow' :
            el.src = prefix + 'circley.png';
            break;

        default :
            el.src = prefix + 'circlew.png';
            break;
        }

    } else if (document.getElementById('shape').value === 'rect') {
        switch (document.getElementById('color').value) {
        case 'white' :
            el.src = prefix + 'rectanglew.jpg';
            break;

        case 'red' :
            el.src = prefix + 'rectangler.jpg';
            break;

        case 'blue' :
            el.src = prefix + 'rectangleb.jpg';
            break;

        case 'purple' :
            el.src = prefix + 'rectanglep.jpg';
            break;

        case 'green' :
            el.src = prefix + 'rectangleg.jpg';
            break;

        case 'orange' :
            el.src = prefix + 'rectangleo.jpg';
            break;

        case 'yellow' :
            el.src = prefix + 'rectangley.jpg';
            break;

        default :
            el.src = prefix + 'rectanglew.jpg';
        }
    }
}

function  ResizeImg(sens) {
    //"use strict";

    if (sens === 'gauche') {
        value -= 5;
    } else if (sens === 'droite') {
        value += 5;
    }

    scalex = answerImg.width + value; // New picture width

    var ratio = answerImg.height / answerImg.width;
    scaley = scalex * ratio; // New picture height proportional to width

    if (scalex > 70 && scaley > 70) { // Not resize too small or negativ
        if (scalex < 810 && scaley < 810) {

            for (j = 0 ; j < indice ; j++) {
                var imgN = 'img' + j;
                var selectedZone = document.getElementById(imgN);

                if (selectedZone) {

                    var left = parseInt(selectedZone.style.left.substr(0, selectedZone.style.left.indexOf('p')));
                    var top = parseInt(selectedZone.style.top.substr(0, selectedZone.style.top.indexOf('p')));

                    newx = scalex * (left - answerImg.offsetLeft) / answerImg.width;
                    newy = scaley * (top - answerImg.offsetTop) / answerImg.height;

                    if (left < answerImg.offsetLeft || top < answerImg.offsetTop ||
                        left > (answerImg.offsetLeft + answerImg.width)
                        || top > (answerImg.offsetTop + answerImg.height)) {

                        newx = newy = 10;
                    }

                    selectedZone.style.left = String(newx + answerImg.offsetLeft) + 'px';
                    selectedZone.style.top = String(newy + answerImg.offsetTop) + 'px';

                    var size = scalex / answerImg.width;
                    cible = selectedZone;
                    resizing = true;
                    ResizePointer(sens, size);
                }
            }
            answerImg.width = scalex;
            answerImg.height = scaley;
        }
    }
    cible = null;
}

function  ResizePointer(sens, diam) {
    //"use strict";

    if (sens == 'gauche') {
        cible.width -= diam;
    } else if (sens == 'droite') {
        cible.width += diam;
    }

    if (cible.width < 10) { // Not too small or negatif
        cible.width = 10;
    }

    cible.height += cible.width * cible.height / cible.height; // Proportional width/height
}

function MouseSens(event) {
    xPrecedent = x;
    yPrecedent = y;

    if (event.x !== undefined && event.y !== undefined) { // IE
        x = event.layerX;
        y = event.layerY;
    } else { // Firefox
        x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    x -= answerImg.offsetLeft; // MouseX position
    y -= answerImg.offsetTop;  // MouseY position

    if (x < xPrecedent) { // Gauche
        sens = 'gauche';
    } else if (x > xPrecedent) { // Droite
        sens = 'droite';
    }

    return sens;
}

function MoveAnswerZone(e) {

    // Get mouse position
    if (e.x != undefined && e.y != undefined) { // IE
        x = e.layerX;
        y = e.layerY;
    } else { // Firefox
        x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    // Move answer zone to mouse position (cursor center)
    cible.style.left = String(x - (cible.width / 2)) + 'px';
    cible.style.top = String(y - (cible.height / 2)) + 'px';
}

// :::::::::::::::::::::::::::::::::::::::::: EventListener :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

document.addEventListener('keydown', function (e) {
    //"use strict";

    if (e.keyCode === 16) { // Touch MAJ down
        pressMAJ = true;
        document.body.style.cursor = 'nw-resize';
    }

    if (e.keyCode === 17) { // Touch CTRL down
        pressCTRL = true;
        document.body.style.cursor = 'move';
    }

    if (e.keyCode === 83) { // Touch s down
        pressS = true;
        //document.body.style.cursor='suppr';
    }

    if (e.keyCode === 18) { // Touch ALT down
        pressALT = true;
    }
}, false);

document.addEventListener('keyup', function (e) {
    //"use strict";

    if (e.keyCode === 16) { // Touch MAJ up
        pressMAJ = false;
        document.body.style.cursor = 'default';
    }

    if (e.keyCode === 17) { // Touch CTRL up
        pressCTRL = false;
        document.body.style.cursor = 'default';
    }

    if (e.keyCode === 83) { // Touch s up
        pressS = false;
        //document.body.style.cursor='default';
    }

    if (e.keyCode === 18) { // Touch ALT up
        pressALT = false;
    }
}, false);

document.addEventListener('mousemove', function (event) { // To resize/moving answer zones/image
    //"use strict";

    // Moving answer zone
    if (cible && resizing == false) {
        MoveAnswerZone(event);
        moving = true;
    }

    // Resizing answer image
    if (pressMAJ === true) {
        ResizeImg(MouseSens(event));
    }

    // Resizing answer zone
    if (pressALT === true && allow === true) {
        ResizePointer(MouseSens(event),5);
        resizing = true;
    }
});

document.addEventListener('click', function (e) { // To add/delete answer zones
    //"use strict";

    // Get the clicked answer zone
    for (j = 0 ; j < indice ; j++) {
        if (e.target.id == 'img' + j) {
            cible = e.target;
            allow = true;
            document.onmousedown = function () { // Stop selection during move/resize
                return false;
            };
        }
    }

    // To add an answer zone
    if (pressCTRL === true) {

        // Position de la souris dans la fenetre :
        if (e.x !== undefined && e.y !== undefined) { // IE
            mousex = e.layerX;
            mousey = e.layerY;
        } else { // Firefox
            mousex = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            mousey = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        // If out of the image
        if ((mousex + 10) > (answerImg.offsetLeft + answerImg.width) || (mousex - 10) < (answerImg.offsetLeft) ||
            (mousey + 10) > (answerImg.offsetTop + answerImg.height) || (mousey - 10) < (answerImg.offsetTop)) {

            if (language.indexOf('fr') > -1) {
                alert('Vous devez mettre la zone de reponse complète DANS l\'image ...');
            } else {
                alert('You must put all the answer zone INSIDE the picture ...');
            }
            document.body.style.cursor = 'default';
        } else {

            var img = new Image();

            img.style.position = 'absolute';
            img.style.left = String(mousex - 10) + 'px';
            img.style.top = String(mousey - 10) + 'px';

            img.id = 'img' + indice;
            indice++;

            img.src = el.src;

            document.body.appendChild(img);

            point[img.id] = document.getElementById('points').value;
        }
        pressCTRL = false;
    }

    // To delete an answer zone
    if (pressS === true) {

        for (j = 0 ; j < indice ; j++) {
            if (e.target.id == 'img' + j) {
                var image = document.getElementById(e.target.id);
                image.parentNode.removeChild(image);
                break;
            }
        }
        pressS = false;
    }

    document.onmousedown = function () { // Restart selection
        resizing = false; // Stop resizing && allow moving
        return true;
    };

    // To stop moving
    if (moving === true && allow === true) {
        cible = null;
        moving = false;
    }
}, false);