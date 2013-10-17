var el = $('#movable'); // To get the shape and the color of the answer zone
var grade = 0; // Number of answer zone
var imgx; // Coord x of the answer zone
var imgy; // Coord y of the answer zone
var j; // For for instruction
var point = {}; // The score of coords
var pressS; // If key s pressed or not
var selectAnswer; // To Resize the selected answer zone with the mouse wheel
var scalex = 0; // Width of the image after resize
var scaley = 0; // Height of the image after resize
var value = 0; // Size of the resizing

// Instructions aren't displayed (by default) for more visibility
$('#Instructions').css({"display" : "none"});
$('#Order').css({"display" : "inline-block"});
$('#hide').css({"display" : "none"});

// If click, instructions are displayed
function DisplayInstruction() {
    $('#Instructions').css({"display" : "inline-block"});
    $('#Order').css({"display" : "none"});
    $('#hide').css({"display" : "inline-block"});
}

// If click, instructions are hidden
function HideInstruction() {
    $('#Instructions').css({"display" : "none"});
    $('#hide').css({"display" : "none"});
    $('#Order').css({"display" : "inline-block"});
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

            // Remove the old image
            $('#AnswerImage').remove();

            // Create a new image
            var answerImg = new Image();

            // Set its new attributes
            $(answerImg).attr("id", "AnswerImage");
            $(answerImg).attr('src', data);

            // Add it to the page
            $('#Answer').append(answerImg);

            // When new image is loaded
            $(answerImg).load(function () {

                // Get its real size
                realw = $(answerImg).prop('naturalWidth');
                realh = $(answerImg).prop('naturalHeight');

                maxSize = $('#Answer').parent('div').width();

                // If its bigger than width of the page, resize the image
                if (realw > maxSize) {
                    scalex = maxSize;

                    // To keep the ratio
                    var ratio = realh / realw;
                    scaley = scalex * ratio;

                    $(answerImg).attr('width', scalex);
                    $(answerImg).attr('height', scaley);
                } else {
                    $(answerImg).attr('width', realw);
                    $(answerImg).attr('height', realh);
                }

                // Make the new image resizable
                $(answerImg).resizable({
                    aspectRatio: true,
                    minWidth: 70,
                    maxWidth: maxSize
                });
            });
        }
    });
}

// Display the selected picture
function LoadPic(path, prefx, iddoc) {

    // Selected document label in the list
    var select = $("*[id$='"+iddoc+"'] option:selected").text();

    // Get the matching url for a given label in order to load the new image
    sendData(select, path, prefx);

    // New picture load, initialization vars and remove previous answer zones
    value = 0;
    grade = 0;
    point = {};

    for (j = 0 ; j < grade ; j++) {
        if ($('#img' + j)) {
            $('#img' + j).remove();
        }
    }
}

$(function () {

    // Make the answer zones draggable
    $('#movable').draggable({
        containment : '#AnswerImage',
        cursor : 'move',

        // When stop drag
        stop: function(event, ui) {
            if($('#AnswerImage').length){

                $('#Answer').append('<div id="dragContainer' + grade +
                    '"><i class="icon-move" style="cursor: move; position: absolute; left: -10px; top: -15px;"></i></div>');

                var stoppos = $(this).position();

                // Create a new image
                var img = new Image();

                // Give it id corresonding of numbers of previous answer
                img.id = 'img' + grade;


                // With the url of the dragged image
                $(img).attr('src', el.attr('src'));


                // Add it to the page
                $('#dragContainer' + grade).append(img);


                imgx = parseInt(stoppos.left);
                imgx -= $('#Answer').position().left; // $('#Answer').prop('offsetLeft');

                // Position y answer zone
                imgy = parseInt(stoppos.top);
                imgy -= $('#Answer').position().top;

                // With the position of the dragged image
                $('#dragContainer' + grade).css({
                    "position" : "absolute",
                    "left" : String(imgx) + 'px',
                    "top"  : String(imgy) + 'px'
                });


                // Make the new answer zone draggable and save its new position when stop drag
                $(img).resizable({
                    aspectRatio: true,
                    minWidth: 10,
                    maxWidth: 500
                });

                $('#dragContainer' + grade).draggable({
                    containment : '#AnswerImage',
                    cursor : 'move',
                    handle : 'i',

                    stop: function(event, ui) {
                        $(img).css("left", $(this).css("left"));
                        $(img).css("top", $(this).css("top"));
                    }
                });

                grade++;

                // Alter symbol score in order to insert right score into the database
                var score = $('#points').val().replace(/[.,]/, '/');

                // Save the score matching to an answer zone (thanks to its id)
                point[img.id] = score;
            }
            // If add a new answer zone, the reference image go back to its initial place
            if (event.target.id == 'movable') {
                el.css({
                    "left" : "0px",
                    "top"  : "0px"
                });
            }
        }
    });
});

// Check if the score is a correct number
function CheckScore(message) {

    if (/^\d+(?:[.,]\d+)?$/.test($('#points').val()) == false) {
        alert(message);
        el.css({"visibility" : "hidden"}); // Answer zone not visible
    } else {
        el.css({"visibility" : "visible"});// Answer zone visible
    }
}

// Submit form without an empty field
function Check(noTitle, noQuestion, noImg, noAnswerZone, questiontitle, invite) {

    var empty = false; // Answer zone aren't defined

    for (j = 0 ; j < grade ; j++) {

        // If at least one answer zone exist
        if ($('#img' + j).length > 0) {
            empty = true;
            break;
        }
    }

    // No title
    if ($('#' + questiontitle).val() == '') {
        alert(noTitle);
        return false;
    } else {

        // No question asked
        if (tinyMCE.get(invite).getContent() == '') {
            alert(noQuestion);
            return false;
        } else {

            // No picture load
            if ($('#AnswerImage').length == 0) {
                alert(noImg);
                return false;
            } else {

                // No answer zone
                if (empty == false) {
                    alert(noAnswerZone);
                    return false;
                } else {

                    // Submit if required fields not empty
                    $('#imgwidth').val($('#AnswerImage').width()); // Pass width of the image to the controller
                    $('#imgheight').val($('#AnswerImage').height()); // Pass height of the image to the controller

                    for (j = 0 ; j < grade ; j++) {

                        var imgN = 'img' + j;
                        var selectedZone = $('#' + imgN); // An answer zone
                        var container = $('#dragContainer' + j);

                        if (selectedZone.length) { // If at least one answer zone is defined

                            var position = selectedZone;

                            if (selectedZone.css("left") == 'auto') {
                                position = container;
                            }

                            // Position x answer zone
                            imgx = parseInt(position.css("left").substring(0, position.css("left").indexOf('p')));

                            // Position y answer zone
                            imgy = parseInt(position.css("top").substring(0, position.css("top").indexOf('p')));

                            // Concatenate informations of the answer zones
                            var val = selectedZone.attr("src") + ';' + imgx + '_' + imgy + '-' + point[imgN] + '~' + selectedZone.prop("width");

                            // And send it to the controller
                            $('#coordsZone').val($('#coordsZone').val() + val + ',');
                        }
                    }
                }
            }
        }
    }
}

// Change the shape and the color of the answer zone
function changezone(prefix) {

    if ($('#shape').val() == 'circle') {
        switch ($('#color').val()) {
        case 'white' :
            el.attr("src", prefix + 'circlew.png');
            $('#color').css({ 'background-color' : '#FFFFFF' });
            break;

        case 'red' :
            el.attr("src", prefix + 'circler.png');
            $('#color').css({ 'background-color' : '#FF0000' });
            break;

        case 'blue' :
            el.attr("src", prefix + 'circleb.png');
            $('#color').css({ 'background-color' : '#002FFF' });
            break;

        case 'purple' :
            el.attr("src", prefix + 'circlep.png');
            $('#color').css({ 'background-color' : '#8B008B' });
            break;

        case 'green' :
            el.attr("src", prefix + 'circleg.png');
            $('#color').css({ 'background-color' : '#008600' });
            break;

        case 'orange' :
            el.attr("src", prefix + 'circleo.png');
            $('#color').css({ 'background-color' : '#FF7A00' });
            break;

        case 'yellow' :
            el.attr("src", prefix + 'circley.png');
            $('#color').css({ 'background-color' : '#FFFF09' });
            break;

        default :
            el.attr("src", prefix + 'circlew.png');
            $('#color').css({ 'background-color' : '#FFFFFF' });
            break;
        }

    } else if ($('#shape').val() == 'square') {
        switch ($('#color').val()) {
        case 'white' :
            el.attr("src", prefix + 'squarew.jpg');
            $('#color').css({ 'background-color' : '#FFFFFF' });
            break;

        case 'red' :
            el.attr("src", prefix + 'squarer.jpg');
            $('#color').css({ 'background-color' : '#FF0000' });
            break;

        case 'blue' :
            el.attr("src", prefix + 'squareb.jpg');
            $('#color').css({ 'background-color' : '#002FFF' });
            break;

        case 'purple' :
            el.attr("src", prefix + 'squarep.jpg');
            $('#color').css({ 'background-color' : '#8B008B' });
            break;

        case 'green' :
            el.attr("src", prefix + 'squareg.jpg');
            $('#color').css({ 'background-color' : '#008600' });
            break;

        case 'orange' :
            el.attr("src", prefix + 'squareo.jpg');
            $('#color').css({ 'background-color' : '#FF7A00' });
            break;

        case 'yellow' :
            el.attr("src", prefix + 'squarey.jpg');
            $('#color').css({ 'background-color' : '#FFFF09' });
            break;

        default :
            el.attr("src", prefix + 'squarew.jpg');
            $('#color').css({ 'background-color' : '#FFFFFF' });
        }
    }
}

// Key press for delete an answer zone
document.addEventListener('keydown', function (e) {
    if (e.keyCode === 83) { // Touch s down
        pressS = true;
    }
}, false);

document.addEventListener('keyup', function (e) {
    if (e.keyCode === 83) { // Touch s up
        pressS = false;
    }
}, false);

document.addEventListener('click', function (e) {

    // To delete an answer zone
    if (pressS === true) {

        for (j = 0 ; j < grade ; j++) {
            if ($(e.target).hasClass('icon-move')) {
                $(e.target).parent('div').remove();
                break;
            }
        }
        pressS = false;
    }
}, false);

function addPicture(url) {
    $.ajax({
            type: "POST",
            url: url,
            cache: false,
            success: function (data) {
                picturePop(data);
            }
        });
}

function picturePop(data) {

    $('body').append(data);

}

$(document.body).on('hidden.bs.modal', function () {
    $('#modaladdpicture').remove();
});
