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
$('#Order').css({"display" : "block"});
$('#hide').css({"display" : "none"});

// Initialize reference answer zone position
el.css({
    "left" : "75px",
    "top"  : "40px"
});

// If click, instructions are displayed
function DisplayInstruction() {
    $('#Instructions').css({"display" : "block"});
    $('#Order').css({"display" : "none"});
    $('#hide').css({"display" : "block"});
}

// If click, instructions are hidden
function HideInstruction() {
    $('#Instructions').css({"display" : "none"});
    $('#hide').css({"display" : "none"});
    $('#Order').css({"display" : "block"});
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

                // If its bigger than width of the page, resize the image
                if (realw > 660) {
                    scalex = 660;

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
                    maxWidth: 660
                });
            });
        }
    });
}

// Display the selected picture
function LoadPic(path, prefx, iddoc) {

    // Selected document label in the list
    var select = $("*[id$='"+iddoc.id+"'] option:selected").text();

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
                var margin = $('#AnswerArray').offset().top - $('#AnswerImage').offset().top;

                var stoppos = $(this).position();

                // Create a new image
                var img = new Image();

                // With the position of the dragged image
                $(img).css({
                    "position" : "absolute",
                    "left" : String(stoppos.left) + 'px',
                    "top"  : String(stoppos.top + margin) + 'px'
                });

                // Give it id corresonding of numbers of previous answer
                img.id = 'img' + grade;
                grade++;

                // With the url of the dragged image
                $(img).attr('src', el.attr('src'));

                // Add it to the page
                $('#Answer').append(img);

                // Make the new answer zone draggable and save its new position when stop drag
                $(img).resizable({
                    aspectRatio: true,
                    minWidth: 10,
                    maxWidth: 500
                })
                .parent()
                .draggable({
                    containment : '#AnswerImage',
                    cursor : 'move',

                    stop: function(event, ui) {
                        $(img).css("left", $(this).css("left"));
                        $(img).css("top", $(this).css("top"));
                    }
                });

                // Alter symbol score in order to insert right score into the database
                var score = $('#points').val().replace(/[.,]/, '/');

                // Save the score matching to an answer zone (thanks to its id)
                point[img.id] = score;
            }
            // If add a new answer zone, the reference image go back to its initial place
            if (event.target.id == 'movable') {
                el.css({
                    "left" : "75px",
                    "top"  : "40px"
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

    var imgOk = false; // Image is upload
    var questionOk = false; // Question is asked
    var titleOk = false; // Question has a title
    var zoneOk = false; // Answer zones are defined
    var empty = false; // Answer zone aren't defined

    for (j = 0 ; j < grade ; j++) {

        var choice = $('#img' + j);

        // If at least one answer zone exist
        if (choice.length > 0) {
            empty = true;
            break;
        }
    }

    // No title
    if ($('#' + questiontitle).val() == '') {
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
    if ($('#AnswerImage').attr('src').indexOf('users_document') == -1 && titleOk == true && questionOk == true) {
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
        $('#imgwidth').val($('#AnswerImage').width()); // Pass width of the image to the controller
        $('#imgheight').val($('#AnswerImage').height()); // Pass height of the image to the controller

        for (j = 0 ; j < grade ; j++) {

            var imgN = 'img' + j;
            var selectedZone = $('#' + imgN); // An answer zone

            if (selectedZone.length) { // If at least one answer zone is defined

                imgx = parseInt(selectedZone.css("left").substring(0, selectedZone.css("left").indexOf('p'))) +
                    (selectedZone.prop("width") / 2);
                imgx -= $('#AnswerImage').prop('offsetLeft'); // Position x answer zone

                imgy = parseInt(selectedZone.css("top").substring(0, selectedZone.css("top").indexOf('p'))) +
                    (selectedZone.prop("height") / 2);
                imgy -= $('#AnswerImage').prop('offsetTop'); // Position y answer zone

                // Concatenate informations of the answer zones
                var val = selectedZone.attr("src") + ';' + imgx + '_' + imgy + '-' + point[imgN] + '~' + selectedZone.prop("width");

                // And send it to the controller
                $('#coordsZone').val($('#coordsZone').val() + val + ',');
            }
        }
        // Then submit the form
        $('#InterGraphForm').submit();
    }
}

// Change the shape and the color of the answer zone
function changezone(prefix) {

    if ($('#shape').val() == 'circle') {
        switch ($('#color').val()) {
        case 'white' :
            el.attr("src", prefix + 'circlew.png');
            break;

        case 'red' :
            el.attr("src", prefix + 'circler.png');
            break;

        case 'blue' :
            el.attr("src", prefix + 'circleb.png');
            break;

        case 'purple' :
            el.attr("src", prefix + 'circlep.png');
            break;

        case 'green' :
            el.attr("src", prefix + 'circleg.png');
            break;

        case 'orange' :
            el.attr("src", prefix + 'circleo.png');
            break;

        case 'yellow' :
            el.attr("src", prefix + 'circley.png');
            break;

        default :
            el.attr("src", prefix + 'circlew.png');
            break;
        }

    } else if ($('#shape').val() == 'rect') {
        switch ($('#color').val()) {
        case 'white' :
            el.attr("src", prefix + 'rectanglew.jpg');
            break;

        case 'red' :
            el.attr("src", prefix + 'rectangler.jpg');
            break;

        case 'blue' :
            el.attr("src", prefix + 'rectangleb.jpg');
            break;

        case 'purple' :
            el.attr("src", prefix + 'rectanglep.jpg');
            break;

        case 'green' :
            el.attr("src", prefix + 'rectangleg.jpg');
            break;

        case 'orange' :
            el.attr("src", prefix + 'rectangleo.jpg');
            break;

        case 'yellow' :
            el.attr("src", prefix + 'rectangley.jpg');
            break;

        default :
            el.attr("src", prefix + 'rectanglew.jpg');
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
            if (e.target.id == 'img' + j) {
                $("#" + e.target.id).remove();
                break;
            }
        }
        pressS = false;
    }
}, false);