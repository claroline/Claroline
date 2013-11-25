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
                    '"><i class="icon-move" style="cursor: move; position: absolute; left: -10px; top: -15px;"></i>'
                    + '<p id="num' + parseInt(grade + 1) +'" style="position: absolute; left: 5px; top: -20px;">'
                    + parseInt(grade + 1) + '</p></div>');

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

                // Alter symbol score in order to insert right score into the database
                var score = $('#points').val().replace(/[.,]/, '/');

                // Save the score matching to an answer zone (thanks to its id)
                point[img.id] = score;

                var infos = getImageInformations($(img).attr('src'));

                alreadyPlacedAnswersZone(infos['shape'], infos['color'], infos['pathImg'], score);

                grade++;
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

    if ($("*[id$='_penalty']").length > 0) {
        $("*[id$='_penalty']").val($("*[id$='_penalty']").val().replace(/[-]/, ''));
    }

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
                    $('#imagewidth').val($('#AnswerImage').width()); // Pass width of the image to the controller
                    $('#imageheight').val($('#AnswerImage').height()); // Pass height of the image to the controller

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

    var shape = $('#shape').val();
    var color = $('#color').val();
    var target = el;

    switchColorShape(prefix, shape, color, target, $('#color'));
}

function switchColorShape(prefix, shape, color, target, targetColor) {
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            target.attr("src", prefix + 'circlew.png');
            targetColor.css({ 'background-color' : '#FFFFFF' });
            break;

        case 'red' :
            target.attr("src", prefix + 'circler.png');
            targetColor.css({ 'background-color' : '#FF0000' });
            break;

        case 'blue' :
            target.attr("src", prefix + 'circleb.png');
            targetColor.css({ 'background-color' : '#002FFF' });
            break;

        case 'purple' :
            target.attr("src", prefix + 'circlep.png');
            targetColor.css({ 'background-color' : '#8B008B' });
            break;

        case 'green' :
            target.attr("src", prefix + 'circleg.png');
            targetColor.css({ 'background-color' : '#008600' });
            break;

        case 'orange' :
            target.attr("src", prefix + 'circleo.png');
            targetColor.css({ 'background-color' : '#FF7A00' });
            break;

        case 'yellow' :
            target.attr("src", prefix + 'circley.png');
            targetColor.css({ 'background-color' : '#FFFF09' });
            break;

        default :
            target.attr("src", prefix + 'circlew.png');
            targetColor.css({ 'background-color' : '#FFFFFF' });
            break;
        }

    } else if (shape == 'square') {
        switch (color) {
        case 'white' :
            target.attr("src", prefix + 'squarew.jpg');
            targetColor.css({ 'background-color' : '#FFFFFF' });
            break;

        case 'red' :
            target.attr("src", prefix + 'squarer.jpg');
            targetColor.css({ 'background-color' : '#FF0000' });
            break;

        case 'blue' :
            target.attr("src", prefix + 'squareb.jpg');
            targetColor.css({ 'background-color' : '#002FFF' });
            break;

        case 'purple' :
            target.attr("src", prefix + 'squarep.jpg');
            targetColor.css({ 'background-color' : '#8B008B' });
            break;

        case 'green' :
            target.attr("src", prefix + 'squareg.jpg');
            targetColor.css({ 'background-color' : '#008600' });
            break;

        case 'orange' :
            target.attr("src", prefix + 'squareo.jpg');
            targetColor.css({ 'background-color' : '#FF7A00' });
            break;

        case 'yellow' :
            target.attr("src", prefix + 'squarey.jpg');
            targetColor.css({ 'background-color' : '#FFFF09' });
            break;

        default :
            target.attr("src", prefix + 'squarew.jpg');
            targetColor.css({ 'background-color' : '#FFFFFF' });
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

                var containerID = $(e.target).parent('div').attr('id');
                var numToDel = parseInt(containerID.substring(containerID.indexOf('er') + 2, containerID.indexOf('er') + 3)) + 1;

                $('#AlreadyPlacedArray').find('td:contains("' + numToDel + '")').parent('tr').remove();

                setOrderAfterDel();

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

function alreadyPlacedAnswersZone(shape, color, pathImg, point) {

    var contenu = '<tr><td class="classic">' + (parseInt(grade) + 1) + '</td><td class="classic">';

    if (shape == 'square') {
        contenu += '<select class="form-control" id="shape' + grade + '" size="1" onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">\n\
                        <option value="circle">' + translations['tradCircle'] + '</option>\n\
                        <option value="square" selected>' + translations['tradSquare'] + '</option>\n\
                    </select></td>'
    } else {
        contenu += '<select class="form-control" id="shape' + grade + '" size="1" onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">\n\
                        <option value="circle" selected>' + translations['tradCircle'] + '</option>\n\
                        <option value="square">' + translations['tradSquare'] + '</option>\n\
                    </select></td>';
    }

    contenu += '<td class="classic">';

    if (color == 'r') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FF0000" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'b') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #002FFF" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'p') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #8B008B" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'g') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #008600" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'o') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FF7A00" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'y') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FFFF09" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FFFFFF" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    }

    contenu += '<option value="white"  style="background-color:#FFFFFF;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="red"    style="background-color:#FF0000;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="blue"   style="background-color:#002FFF;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="purple" style="background-color:#8B008B;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="green"  style="background-color:#008600;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="orange" style="background-color:#FF7A00;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="yellow" style="background-color:#FFFF09;"> &nbsp;&nbsp;&nbsp; </option>\n\
            </select></td>';

    contenu += '<td class="classic"><input class="form-control" type="TEXT" id="points' + grade + '" value="'
                    + point + '" onblur="changePoints(\'' + translations['tradWrongPoint'] + '\', this);"></td></tr>';

    $('#AlreadyPlacedArray').find('tbody').append(contenu);
}

function alterAlreadyPlaced(pathImg, alterSelect) {

    var newParam = $('#' + alterSelect.id).val();
    var numChange = alterSelect.id.substring(5);
    var idImgToChange = 'img' + numChange;
    var shape, color;

    if (newParam == 'square' || newParam == 'circle') {
        shape = newParam;
        color = $('#color' + numChange).val();
    } else {
        color = newParam;
        shape = $('#shape' + numChange).val();
    }

    switchColorShape(pathImg, shape, color, $('#' + idImgToChange), $('#color' + numChange));
}

function changePoints(tradWrongPoint, targetChange) {
    var numChange = targetChange.id.substring(6);
    var idImgToChange = 'img' + (parseInt(numChange) - 1);

    point[idImgToChange] = $('#points' + numChange).val();
    CheckScore(tradWrongPoint);
}

function getImageInformations(src) {
    var infos = {};

    infos['shape'] = src.substring(src.indexOf('c/') + 2, (src.indexOf('c/') + 8));
    infos['color'] = src.substring(src.indexOf('.') - 1, src.indexOf('.'));
    infos['pathImg'] = src.substring(0, src.indexOf('c/') + 2);

    return (infos);
}

function setOrderAfterDel() {
    grade = 0;
    var oldPoints = point;
    point = {};

    $('#AlreadyPlacedArray').find('tr:not(:first)').each(function () {
        num = grade + 1;
        $(this).find('td').eq(0).replaceWith('<td class="classic">' + num + '</td>');
        grade++;
    });

    grade = 0;

    $("*[id^='dragContainer']").each(function () {
        num = grade + 1;
        $(this).attr('id', String('dragContainer' + grade));
        $(this).find('p').replaceWith(String('<p id="num' + num +'" style="position: absolute; left: 5px; top: -20px;">' + num + '</p>'));
        grade++;
    });

    grade = 0;

    $("*[id^='img']").each(function () {
        var oldId = $(this).attr('id');
        num = grade + 1;
        $(this).attr('id', String('img' + grade));
        point[$(this).attr('id')] = oldPoints[oldId];
        grade++;
    });

    grade = 0;

    $("*[id^='shape']").each(function () {
        if ($(this).attr('id').length > 5) {
            $(this).attr('id', String('shape' + grade));
            grade++;
        }
    });

    grade = 0;

    $("*[id^='color']").each(function () {
        if ($(this).attr('id').length > 5) {
            $(this).attr('id', String('color' + grade));
            grade++;
        }
    });

    grade = 0;

    $("*[id^='points']").each(function () {
        if ($(this).attr('id').length > 6) {
            $(this).attr('id', String('points' + grade));
            grade++;
        }
    });
}