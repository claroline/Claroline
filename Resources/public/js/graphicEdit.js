// Get the already placed right answer zone
window.onload = function () {

    var infos = $('#info').val();
    var i = infos.substr(0, infos.indexOf('~'));
    infos = infos.substr(infos.indexOf('~') + 1);
    var info = infos.split('^');

    for (var x = 0 ; x < i ; x++) {
        var content = info[x].split(';');
        position(content[0], content[1], x, content[2], content[3], content[4], content[5]);
    }
};

function position(shape, color, i, prefix, value, size, points) {

    // Set the shape/color of the right answer zone already placed
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            $('#img' + i).attr('src', prefix + 'circlew.png');
            break;

        case 'red' :
            $('#img' + i).attr('src', prefix + 'circler.png');
            break;

        case 'blue' :
            $('#img' + i).attr('src', prefix + 'circleb.png');
            break;

        case 'purple' :
            $('#img' + i).attr('src', prefix + 'circlep.png');
            break;

        case 'green' :
            $('#img' + i).attr('src', prefix + 'circleg.png');
            break;

        case 'orange' :
            $('#img' + i).attr('src', prefix + 'circleo.png');
            break;

        case 'yellow' :
            $('#img' + i).attr('src', prefix + 'circley.png');
            break;

        default :
            $('#img' + i).attr('src', prefix + 'circlew.png');
            break;
        }

    } else if (shape == 'square') {
        switch (color) {
        case 'white' :
            $('#img' + i).attr('src', prefix + 'squarew.jpg');
            break;

        case 'red' :
            $('#img' + i).attr('src', prefix + 'squarer.jpg');
            break;

        case 'blue' :
            $('#img' + i).attr('src', prefix + 'squareb.jpg');
            break;

        case 'purple' :
            $('#img' + i).attr('src', prefix + 'squarep.jpg');
            break;

        case 'green' :
            $('#img' + i).attr('src', prefix + 'squareg.jpg');
            break;

        case 'orange' :
            $('#img' + i).attr('src', prefix + 'squareo.jpg');
            break;

        case 'yellow' :
            $('#img' + i).attr('src', prefix + 'squarey.jpg');
            break;

        default :
            $('#img' + i).attr('src', prefix + 'squarew.jpg');
        }
    }

    // Set the width of the right answer zone already placed
    $('#img' + i).attr('width', size);

    // Set the position of the right answer zone already placed
    var x = value.substr(0, value.indexOf(','));
    var y = value.substr(value.indexOf(',') + 1);

    $('#dragContainer' + grade).css({
        "position" : "absolute",
        "left" : x + 'px',
        "top"  : y + 'px'
    });

    // Set the id of the right answer zone already placed
    var name = 'img' + i;

    // Set the points of the right answer zone already placed
    point[name] = points;

    // Make answer image resizable
    $('#AnswerImage').resizable({
        aspectRatio: true,
        minWidth: 70,
        maxWidth: 660
    });

    // Make the already placed right answer zone resizable and draggable and save new postion when stop drag
    $('#img' + i).resizable({
        aspectRatio: true,
        minWidth: 10,
        maxWidth: 500
    });

    $('#dragContainer' + grade).draggable({
        containment : '#AnswerImage',
        cursor : 'move',
        handle : 'i',

        stop: function(event, ui) {
            $('#img' + i).css("left", $(this).css("left"));
            $('#img' + i).css("top", $(this).css("top"));
        }
    });

    $('#dragContainer' + grade).append('<p id="num' + parseInt(grade + 1) +'" style="position: absolute; left: 5px; top: -20px;">'
        + parseInt(grade + 1) + '</p>');

    alreadyPlacedAnswersZoneEdit(shape, color, prefix, points);

    grade++;
}

function alreadyPlacedAnswersZoneEdit(shape, color, pathImg, point) {

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

    if (color == 'red') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FF0000" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'blue') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #002FFF" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'purple') {
         contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #8B008B" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'green') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #008600" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'orange') {
        contenu += '<select class="form-control" id="color' + grade + '" size="1" style="background-color : #FF7A00" \n\
                        onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">';
    } else if (color == 'yellow') {
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