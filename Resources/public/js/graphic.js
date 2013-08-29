// :::::::::::::::::::::::::::::::::::::::::: Declaration variables :::::::::::::::::::::::::::::::::::::::::::::::::::
var allow = false; // To know if answer zone is selected before resize
var answerImg = document.getElementById('AnswerImage'); // The background answer image
var target; // The selected answer zones
var el = document.getElementById('movable'); // To get the shape and the color of the answer zone
var imgx; // Coord x of the answer zone
var imgy; // Coord y of the answer zone
var grade = 0; // Number of answer zone
var j; // For for instruction
var maxZ = 0; // To put the selected answer zone on top
var mousex; // Position x of the mouse
var mousey; // position y of the mouse
var moving = false; // If move answer zones
var newx; // new width of the image after resize
var newy; // new height of the image after resize
var point = {}; // The score of coords
var pressMAJ; // If key MAJ pressed or not
var pressCTRL; // If key CTRL pressed or not
var pressALT; // If key ALT pressed or not
var pressS; // If key s pressed or not
var resizing = false; // If resize answer zone
var result; // src of the answer image
var direction; // Move of the mouse
var selectAnswer; // to Resize the selected answer zone with the mouse wheel
var scalex = 0; // Width of the image after resize
var scaley = 0; // Height of the image after resize
var value = 0; // Size of the resizing
var x = 0; // Mouse x after move
var xPrev = 0; // Mouse x before move
var y = 0; // Mouse y after move


// Instructions aren't displayed (by default) for more visibility
document.getElementById('Instructions').style.display = 'none';
document.getElementById('Order').style.display = 'block';
document.getElementById('hide').style.display = 'none';

// Initialize reference answer zone position
el.style.left = '75px';
el.style.top = '40px';

// :::::::::::::::::::::::::::::::::::::::::: Functions :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

function DisplayInstruction() { // If click, instructions are displayed
    document.getElementById('Instructions').style.display = 'block';
    document.getElementById('Order').style.display = 'none';
    document.getElementById('hide').style.display = 'block';
}

function HideInstruction() {
    document.getElementById('Instructions').style.display = 'none';
    document.getElementById('hide').style.display = 'none';
    document.getElementById('Order').style.display = 'block';
}

// Get the url's picture matching to the label in the list
function sendData(select, path, prefx) {

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
function LoadPic(path, prefx, iddoc) {

    var list = document.getElementById(iddoc.id);// List of all the user's pictures
    var select = list.options[list.selectedIndex].innerHTML; // Label of the selected picture

    sendData(select, path, prefx);

    // New picture load, initialization var and remove previous answer zones :
    value = 0;

    for (j = 0 ; j < grade ; j++) {
        if (document.getElementById('img' + j)) {
            document.getElementById('img' + j).parentNode.removeChild(document.getElementById('img' + j));
        }
    }
    grade = 0;
    point = {};
}

// Check if the score is a correct number
function CheckScore(message) {

    if (/^\d+(?:[.,]\d+)?$/.test(document.getElementById('points').value) == false) {
        alert(message);
        el.style.visibility = 'hidden'; // Answer zone not visible
    } else {
        el.style.visibility = 'visible';// Answer zone visible
    }
}

// Submit form without an empty field
function Check(noTitle, noQuestion, noImg, noAnswerZone, questiontitle, invite) {

    var imgOk = false; // Image is upload
    var questionOk = false; // Question is asked
    var titleOk = false; // Question has a title
    var zoneOk = false; // Answer zones are defined
    var empty = false; // Answer zone aren't defined

    for (j = 0 ; j < grade ; j++) {

        var name = 'img' + j;
        var choice = document.getElementById(name);

        // If at least one answer zone exist
        if (choice) {
            empty = true;
            break;
        }
    }

    // No title
    if (document.getElementById(questiontitle).value == '') {
        alert(noTitle);
        return false;
    } else {
        titleOk = true;
    }

    // No question asked
    if (tinyMCE.get(invite).getContent() == '' && titleOk == true) {
        alert(noQuestion);
        return false;
    } else {
        questionOk = true;
    }

    // No picture load
    if (document.getElementById('AnswerImage').src.indexOf('users_document') == -1 && titleOk == true && questionOk == true) {
        alert(noImg);
        return false;
    } else {
        imgOk = true;
    }

    // No answer zone
    if (empty == false && imgOk == true && titleOk == true && questionOk == true) {
        alert(noAnswerZone);
        return false;
    } else {
        zoneOk = true;
    }

    // Submit if required fields not empty
    if (imgOk == true && zoneOk == true && titleOk == true && questionOk == true) {
        document.getElementById('imgwidth').value = answerImg.width; // Pass width of the image to the controller
        document.getElementById('imgheight').value = answerImg.height; // Pass height of the image to the controller

        for (j = 0 ; j < grade ; j++) {

            var imgN = 'img' + j;
            var selectedZone = document.getElementById(imgN); // An answer zone

            if (selectedZone) { // If at least one answer zone is defined

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
        // Then submit the form
        document.getElementById('InterGraphForm').submit();
    }
}

// Change the shape and the color of the answer zone
function changezone(prefix) {

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

// Resize the answer image
function  ResizeImg(direction) {

    if (direction === 'gauche') {
        value -= 10;
    } else if (direction === 'droite') {
        value += 10;
    }

    scalex = answerImg.width + value; // New picture width

    var ratio = answerImg.height / answerImg.width;
    scaley = scalex * ratio; // New picture height proportional to width

    if (scalex > 70 && scaley > 70) { // Not resize too small or negative
        if (scalex < 810 && scaley < 810) { // Not resize too big or out window

            for (j = 0 ; j < grade ; j++) {
                var imgN = 'img' + j;
                var selectedZone = document.getElementById(imgN); // An answer zone

                if (selectedZone) { // If at least one answer zone is defined

                    // Position x & y of the current selected answer zone
                    var left = parseInt(selectedZone.style.left.substr(0, selectedZone.style.left.indexOf('p')));
                    var top = parseInt(selectedZone.style.top.substr(0, selectedZone.style.top.indexOf('p')));

                    // New x & y after the resizing of the image
                    newx = scalex * (left - answerImg.offsetLeft) / answerImg.width;
                    newy = scaley * (top - answerImg.offsetTop) / answerImg.height;

                    // If out the image
                    if (left < answerImg.offsetLeft - document.getElementById('Answer').offsetLeft || top <
                        answerImg.offsetTop - document.getElementById('Answer').offsetLeft ||
                        left > (answerImg.offsetLeft + answerImg.width) ||
                        top > (answerImg.offsetTop + answerImg.height)) {

                        newx = newy = 10; // Default value
                    }

                    // Place the answer zone to its new position
                    selectedZone.style.left = String(newx + answerImg.offsetLeft) + 'px';
                    selectedZone.style.top = String(newy + answerImg.offsetTop) + 'px';

                    // Calculate the size of the answer zone proportionally to the new size of the answer image
                    var size = scalex / answerImg.width;

                    // Select the answer zone to resize
                    target = selectedZone;

                    // Disable moving answer zone and enable resizing it
                    resizing = true;

                    // Resize the answer zone
                    ResizePointer(direction, size);
                }
            }
            // Resize the answer image
            answerImg.width = scalex;
            answerImg.height = scaley;
        }
    }
    // Unselect the answer zone
    target = null;
}

// Resize the answer zones
function  ResizePointer(direction, diam) {

    if (target != null) {

        if (direction == 'gauche') {
            target.width -= diam;
        } else if (direction == 'droite') {
            target.width += diam;
        }

        if (target.width < 10) { // Not too small or negative
            target.width = 10;
        }

        target.height += target.width * target.height / target.height; // Resize with proportional width/height
    }
}

// Get the mouse direction
function MouseDirection(event) {

    // Old position of the mouse
    xPrev = x;

    // Get current position of the mouse
    if (event.x !== undefined && event.y !== undefined) { // IE
        x = event.layerX;
        y = event.layerY;
    } else { // Firefox
        x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft - document.getElementById('Answer').offsetLeft;
        y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop - document.getElementById('Answer').offsetTop;
    }

    x -= answerImg.offsetLeft; // MouseX position
    y -= answerImg.offsetTop;  // MouseY position

    if (x < xPrev) { // Gauche
        direction = 'gauche';
    } else if (x > xPrev) { // Droite
        direction = 'droite';
    }

    return direction;
}

// Move the answer zones
function MoveAnswerZone(e) {

    if (target != null) {

        // Get mouse position
        var position = getMousePosition(e);
        var x = position[0];
        var y = position[1];

        if (target.id != 'movable') {
            var posx = x - (document.getElementsByClassName('col-md-9 section-content')[0].offsetLeft + document.getElementById('Answer').offsetLeft);
            var posy = (y + document.documentElement.scrollTop) - (document.getElementById('Answer').offsetTop + document.getElementById('AnswerArray').clientHeight);

            // If out of the image
            if ((posx + 10) > (answerImg.width) || (posx - 10) < (0) || (posy + 10) > (answerImg.height) || (posy - 10) < (0)) {
                target = null;
                moving = false;

            } else {

                // Move answer zone to mouse position (cursor center)
                target.style.left = String(posx - (target.width / 2)) + 'px';
                target.style.top = String(posy - (target.height / 2)) + 'px';
           }

        } else {

            x -= document.getElementsByClassName('col-md-9 section-content')[0].offsetLeft + document.getElementById('Answer').offsetLeft;
            y -= document.getElementById('AnswerArray').offsetTop - document.documentElement.scrollTop + document.getElementById('AnswerArray').clientHeight;

            // Move answer zone to mouse position (cursor center)
            target.style.left = String(x - (target.width / 2)) + 'px';
            target.style.top = String(y - (target.height / 2)) + 'px';

            pressCTRL = true;
        }
    }
}

// :::::::::::::::::::::::::::::::::::::::::: EventListener :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

document.addEventListener('keydown', function (e) {

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

    // Moving answer zone
    if (target && resizing == false) {
        MoveAnswerZone(event);
        moving = true;
    }

    // Resizing answer image
    if (pressMAJ === true) {
        ResizeImg(MouseDirection(event));
    }

    // Resizing answer zone
    if (pressALT === true && allow === true) {
        ResizePointer(MouseDirection(event), 10);
        resizing = true;
    }

    // Resize answer zones with mouse wheel
    for (j = 0 ; j < grade ; j++) {
        if (event.target.id == 'img' + j) {
            selectAnswer = document.getElementById(event.target.id);

            // Add event listener depending on browser
            if (selectAnswer.addEventListener) {
                selectAnswer.addEventListener("mousewheel", MouseWheelCoords, false);
                selectAnswer.addEventListener("DOMMouseScroll", MouseWheelCoords, false);
            } else {
                selectAnswer.attachEvent("onmousewheel", MouseWheelCoords);
            }
        }
    }
});

document.addEventListener('click', function (e) { // To add/delete answer zones

    // Get the clicked answer zone
    for (j = 0 ; j < grade ; j++) {
        if (e.target.id == 'img' + j) {
            target = e.target;
            allow = true;
            maxZ++;
            target.style.zIndex = maxZ;
            document.onmousedown = function () { // Stop selection during move/resize
                return false;
            };
        }
    }

    // To add a new answer zone with drag & drop
    if (e.target.id == 'movable') {
        target = e.target;
        allow = true;
        document.onmousedown = function () { // Stop selection during move/resize
            return false;
        };
    }

    // To add an answer zone
    if (pressCTRL == true && el.style.visibility != 'hidden') {

        // Position of the mouse into the window
        var position = getMousePosition(e);
        var mousex = position[0];
        var mousey = position[1];

        var posx = mousex - (document.getElementsByClassName('col-md-9 section-content')[0].offsetLeft + document.getElementById('Answer').offsetLeft);
        var posy = mousey - (document.getElementById('Answer').offsetTop - document.documentElement.scrollTop + document.getElementById('AnswerArray').clientHeight);

        // If out of the image
        if ((posx + 10) > (answerImg.width) || (posx - 10) < (0) || (posy + 10) > (answerImg.height) || (posy - 10) < (0)) {

            alert(document.getElementById('message').value);

            document.body.style.cursor = 'default';

            // Answer zone go back to its initial place
            el.style.left = '75px';
            el.style.top = '40px';

        } else {

            var img = new Image();

            img.style.position = 'absolute';
            img.style.left = String(posx - 10) + 'px';
            img.style.top = String(posy - 10) + 'px';

            img.id = 'img' + grade;
            grade++;

            img.src = el.src;

            document.getElementById('Answer').appendChild(img);

            // Alter symbol score in order to insert right score into the database
            var score = document.getElementById('points').value.replace(/[.,]/, '/');

            point[img.id] = score;

            // If add a new answer zone, the reference image go back to its initial place
            if (target.id == 'movable') {
                el.style.left = '75px';
                el.style.top = '40px';
            }
        }
        pressCTRL = false;
    }

    // To delete an answer zone
    if (pressS === true) {

        for (j = 0 ; j < grade ; j++) {
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
        target = null;
        moving = false;
    }
}, false);

// Resize image and answer zones with mouse wheel (for all browser)
if (answerImg.addEventListener) {
    answerImg.addEventListener("mousewheel", MouseWheelHandler, false);
    answerImg.addEventListener("DOMMouseScroll", MouseWheelHandler, false);
} else {
    answerImg.attachEvent("onmousewheel", MouseWheelHandler);
}

function MouseWheelHandler(e) {

    // cross-browser wheel delta
    var e = window.event || e;
    var delta = -(Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail))));

    // New width/height
    var finalWidth = answerImg.width + delta * 10;
    var finalHeight = answerImg.height + delta * 10;
    var rax = finalWidth/answerImg.width;
    var ray = finalHeight/answerImg.height;

    if (finalWidth > 70 && finalWidth < 800) {
        answerImg.width = finalWidth;
    }

    for (j = 0 ; j < grade ; j++) {
        var imgN = 'img' + j;
        var selectedZone = document.getElementById(imgN); // An answer zone

        if (selectedZone) { // If at least one answer zone is defined

            // Position x & y of the current selected answer zone
            var left = parseInt(selectedZone.style.left.substr(0, selectedZone.style.left.indexOf('p')));
            var top = parseInt(selectedZone.style.top.substr(0, selectedZone.style.top.indexOf('p')));

            left = left * rax;
            top = top * ray;

            // Calculate the size of the answer zone proportionally to the new size of the answer image
            var size = delta * 2 * rax;

            selectedZone.style.left = String(left + size / 2) + 'px';
            selectedZone.style.top = String(top + size / 2) + 'px';

            // Select the answer zone to resize
            target = selectedZone;

            // Disable moving answer zone and enable resizing it
            resizing = true;

            // Resize the answer zone
            ResizePointer(direction, size);
        }
    }

    // Prevent scrooling the page when resize
    e.preventDefault();

    return false;
}

function MouseWheelCoords(e) {

    // cross-browser wheel delta
    var e = window.event || e;
    var delta = -(Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail))));

    var finalWidth = selectAnswer.width + delta * 10;

    if (finalWidth > 5 && finalWidth < answerImg.width/2) {
        selectAnswer.width = finalWidth;
    }

    e.preventDefault();

    return false;
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