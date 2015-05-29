var el = $('#movable'); // To get the shape and the color of the answer zone
var grade = 0; // Number of answer zone
var count=0; //Value answer zone
var imgx; // Coord x of the answer zone
var imgy; // Coord y of the answer zone
var j; // For for instruction
var point = {}; // The score of coords
var pressS; // If key s pressed or not
var selectAnswer; // To Resize the selected answer zone with the mouse wheel
var scalex = 0; // Width of the image after resize
var scaley = 0; // Height of the image after resize
var value = 0; // Size of the resizing

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
                //Image center
              //  $('#Answer').children('div').css({'margin': 'auto'});
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

    for (j = 0 ; j < grade ; j++) {
        if ($('#dragContainer' + j)) {
            $('#dragContainer' + j).remove();

            $('#AlreadyPlacedArray').find('tr:not(:first)').remove();
        }
    }

    value = 0;
    grade = 0;
    point = {};
    
    //displays the button to add an answer
    $('#addButtonAnswer').css({"display" : "inline"});
}

/**
 * Add an answer zone
 *
 * @param {String} noImage Information no image selected
 * @returns array
 */
 function addAnswerZone(noImage)
 {
    if($('#AnswerImage').length){
        //For edition : put in order if it adds points
        setOrderAfterDel();

        $('#Answer').append('<div id="dragContainer' + grade +
            '"><i class="fa fa-arrows" style="cursor: move; position: absolute; left: -10px; top: -15px;"></i>'
            + '<p style="position: absolute; left: 5px; top: -20px;">'
            + parseInt(count + 1) + '</p></div>');

        //var stoppos = $(this).position();

//        var toppos = $('#Answer').position().top;
//        var leftpos = $('#Answer').find('.ui-wrapper').position().left;

        // Create a new image
        var img = new Image();

        // Give it id corresonding of numbers of previous answer
        img.id = 'img' + grade;


        // With the url of the dragged image
        $(img).attr('src', el.attr('src'));
        
         // With the style of the dragged image
        $(img).attr('style',el.attr('style'));
       
         // Add it to the page
        $('#dragContainer' + grade).append(img);

//        imgx = parseInt(leftpos);
//        imgx -= $('#Answer').position().left; // $('#Answer').prop('offsetLeft');
        imgx=0;
          //  alert(imgx);
        // Position y answer zone
//        imgy = parseInt(toppos);
//        imgy -= $('#Answer').position().top;
        imgy=0;
          //  alert(imgy);
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
        count++;
        //Creation head of the table
        if ($('#AnswerImage').find('#dragContainer0').length > 1) {
         $('#AlreadyPlacedArray').css({"display" : "none"});

        } else {
         $('#AlreadyPlacedArray').css({"display" : "inline"});
        }
    }else{
        alert(noImage);
    }
}

// Check if the score is a correct number
function CheckScore(message, valueOfPoint) {
    var valToTest = '';

    if (valueOfPoint == 'default') {
        valToTest = $('#points').val();
    } else {
        valToTest = valueOfPoint;
    }

    if (/^\d+(?:[.,]\d+)?$/.test(valToTest) == false) {
        alert(message);
        el.css({"visibility" : "hidden"}); // Answer zone not visible
        $('#button_submit').css({"visibility" : "hidden"}); // Validate button not visible
    } else {
        el.css({"visibility" : "visible"});// Answer zone visible
        $('#button_submit').css({"visibility" : "visible"}); // Validate button visible
    }
}

// Submit form without an empty field
function Check( noQuestion, noImg, noAnswerZone, invite) {

    /*if ($("*[id$='_penalty']").length > 0) {
        $("*[id$='_penalty']").val($("*[id$='_penalty']").val().replace(/[-]/, ''));
    }*/

    var empty = false; // Answer zone aren't defined

    for (j = 0 ; j < grade ; j++) {

        // If at least one answer zone exist
        if ($('#img' + j).length > 0) {
            empty = true;
            break;
        }
    }

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
                         //   alert('imgx:'+imgx+' imgy:'+imgy);
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
        case 'black' :
            target.attr("src", prefix + 'circleblack.png');
            targetColor.css({ 'background-color' : '#000000' });
            break;
        case 'white' :
            target.attr("src", prefix + 'circlew.png');
            targetColor.css({ 'background-color' : '#FFFFFF' });
            break;

        case 'red' :
            target.attr("src", prefix + 'circler.png');
            targetColor.css({ 'background-color' : '#C1001F' });
            break;

        case 'blue' :
            target.attr("src", prefix + 'circleb.png');
            targetColor.css({ 'background-color' : '#009CDD' });
            break;

        case 'purple' :
            target.attr("src", prefix + 'circlep.png');
            targetColor.css({ 'background-color' : '#56267D' });
            break;

        case 'green' :
            target.attr("src", prefix + 'circleg.png');
            targetColor.css({ 'background-color' : '#118E3F' });
            break;

        case 'orange' :
            target.attr("src", prefix + 'circleo.png');
            targetColor.css({ 'background-color' : '#C95226' });
            break;

        case 'yellow' :
            target.attr("src", prefix + 'circley.png');
            targetColor.css({ 'background-color' : '#FFEB00' });
            break;
            
        case 'brown' :
            target.attr("src", prefix + 'circlebrown.png');
            targetColor.css({ 'background-color' : '#5A4C41' });
             break;
        default :
            target.attr("src", prefix + 'circleblack.png');
            targetColor.css({ 'background-color' : '#000000' });
            break;
        }

    } else if (shape == 'square') {
        switch (color) {
        case 'black' :  
            target.attr("src", prefix + 'squareblack.png');
            targetColor.css({ 'background-color' : '#000000' });
            break;

        case 'white' :
            target.attr("src", prefix + 'squarew.png');
            targetColor.css({ 'background-color' : '#FFFFFF' });
            break;

        case 'red' :
            target.attr("src", prefix + 'squarer.png');
            targetColor.css({ 'background-color' : '#C1001F' });
            break;

        case 'blue' :
            target.attr("src", prefix + 'squareb.png');
            targetColor.css({ 'background-color' : '#009CDD' });
            break;

        case 'purple' :
            target.attr("src", prefix + 'squarep.png');
            targetColor.css({ 'background-color' : '#56267D' });
            break;

        case 'green' :
            target.attr("src", prefix + 'squareg.png');
            targetColor.css({ 'background-color' : '#118E3F' });
            break;

        case 'orange' :
            target.attr("src", prefix + 'squareo.png');
            targetColor.css({ 'background-color' : '#C95226' });
            break;

        case 'yellow' :
            target.attr("src", prefix + 'squarey.png');
            targetColor.css({ 'background-color' : '#FFEB00' });
            break;
            
        case 'brown' :
            target.attr("src", prefix + 'squarebrown.png');
            targetColor.css({ 'background-color' : '#5A4C41' });
            break;

        default :
             target.attr("src", prefix + 'squareblack.png');
             targetColor.css({ 'background-color' : '#000000' });
        }
    }
}

//// Key press for delete an answer zone
//document.addEventListener('keydown', function (e) {
//    if (e.keyCode === 83) { // Touch s down
//        pressS = true;
//    }
//}, false);
//
//document.addEventListener('keyup', function (e) {
//    if (e.keyCode === 83) { // Touch s up
//        pressS = false;
//    }
//}, false);
//
//document.addEventListener('click', function (e) {
//
//    // To delete an answer zone
//    if (pressS === true) {
//
//        for (j = 0 ; j < grade ; j++) {
//            if ($(e.target).hasClass('fa fa-arrows')) {
//                $(e.target).parent('div').remove();
//
//                var containerID = $(e.target).parent('div').attr('id');
//                var numToDel = parseInt(containerID.substring(containerID.indexOf('er') + 2, containerID.indexOf('er') + 3)) + 1;
//
//                $('#AlreadyPlacedArray').find('td:contains("' + numToDel + '")').parent('tr').remove();
//
//                setOrderAfterDel();
//
//                break;
//            }
 //       }
//        pressS = false;
//    }
//}, false);

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
    var contenu = '<tr><td class="classic" width="25%">' + (parseInt(count) + 1) + '</td><td class="classic">';

    if (shape == 'square') {
        contenu += '<select class="form-control" id="shape' + grade + '" size="1" onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">\n\
                        <option value="circle"> <img src="bundles/ujmexo/images/graphic/circleblack.png"></option>\n\
                        <option value="square" selected>' + translations['tradSquare'] + '</option>\n\
                    </select></td>'
    } else {
        contenu += '<select class="form-control" id="shape' + grade + '" size="1" onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">\n\
                        <option value="circle" selected>' + translations['tradCircle'] + '</option>\n\
                        <option value="square">' + translations['tradSquare'] + '</option>\n\
                    </select></td>';
    }

    contenu += '<td class="classic">';

    if (color == 'white') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FFFFFF" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    }else if (color == 'red') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #C1001F" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    }else if (color == 'blue') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #009CDD" \n\
                         onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'purple') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #56267D" \n\
                         onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'green') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #118E3F" \n\
                         onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'orange') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #C95226" \n\
                         onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'yellow') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FFEB00" \n\
                         onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
     } else if (color == 'brown') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #5A4C41" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #000000" \n\
                         onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    }

   contenu += '<option value="black"  style="background-color:#000000;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="white"  style="background-color:#FFFFFF;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="red"    style="background-color:#C1001F;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="blue"   style="background-color:#009CDD;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="purple" style="background-color:#56267D;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="green"  style="background-color:#118E3F;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="orange" style="background-color:#C95226;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="yellow" style="background-color:#FFEB00;"> &nbsp;&nbsp;&nbsp; </option>\n\
                <option value="brown"  style="background-color:#5A4C41;"> &nbsp;&nbsp;&nbsp; </option>\n\
             </select></td>';

    contenu += '<td class="classic"><input class="form-control" type="TEXT" id="points' + grade + '" value="'
                    + point + '" onblur="changePoints(\'' + translations['tradWrongPoint'] + '\', this);"></td><td class="classic"><a class="btn btn-danger" id="delete'+grade+'"><i class="fa fa-close"></i></a></td></tr>';
    $('#AlreadyPlacedArray').find('tbody').append(contenu);

    $('#delete'+grade).click(function(e) {
        $(this).parent('td').parent('tr').remove();
        var chiffre = $(this).attr('id').replace('delete', '');  
        $('#dragContainer'+chiffre).remove();
        setOrderAfterDel();
        e.preventDefault();
    });
    //indice button delete
   // m++;
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
    var idImgToChange = 'img' + (parseInt(numChange));

    point[idImgToChange] = $('#points' + numChange).val();

    CheckScore(tradWrongPoint, $('#points' + numChange).val());
}

function getImageInformations(src) {
    var infos = {};

    infos['shape'] = src.substring(src.indexOf('c/') + 2, (src.indexOf('c/') + 8));
    infos['color'] = src.substring(src.indexOf('.') - 1, src.indexOf('.'));
    infos['pathImg'] = src.substring(0, src.indexOf('c/') + 2);

    return (infos);
}

function setOrderAfterDel() {
    count = 0;
    var oldPoints = point;
    point = {};

    $('#AlreadyPlacedArray').find('tr:not(:first)').each(function () {
        num = count + 1;
        $(this).find('td').eq(0).replaceWith('<td class="classic">' + num + '</td>');
        count++;
    });

    count = 0;

    $("*[id^='dragContainer']").each(function () {
        num = count + 1;
      //  $(this).attr('id', String('dragContainer' + grade));
        $(this).find('p').replaceWith(String('<p style="position: absolute; left: 5px; top: -20px;">' + num + '</p>'));
        count++;
    });
    $("*[id^='img']").each(function () {
        point[$(this).attr('id')] = oldPoints[$(this).attr('id')];
    });

//    count = 0;
//
//    $("*[id^='img']").each(function () {
//        var oldId = $(this).attr('id');
////        $(this).attr('id', String('img' + count));
//        point[$(this).attr('id')] = oldPoints[oldId];
////        count++;
//    });
//
//    count = 0;
//
//    $("*[id^='shape']").each(function () {
//        if ($(this).attr('id').length > 5) {
//            $(this).attr('id', String('shape' + count));
//            count++;
//        }
//    });
//
//    count = 0;
//
//    $("*[id^='color']").each(function () {
//        if ($(this).attr('id').length > 5) {
//            $(this).attr('id', String('color' + count));
//            count++;
//        }
//    });
//
//    count = 0;
//
//    $("*[id^='points']").each(function () {
//        if ($(this).attr('id').length > 6) {
//            $(this).attr('id', String('points' + count));
//            count++;
//        }
//    });
}

