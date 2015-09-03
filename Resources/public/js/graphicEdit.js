// Get the already placed right answer zone
window.onload = function () {

    var infos = $('#info').val();
    var i = infos.substr(0, infos.indexOf('~'));
    infos = infos.substr(infos.indexOf('~') + 1);
    var info = infos.split('^');
    for (var x = 0 ; x < i ; x++) {
        var content = info[x].split('§§');
        position(content[0], content[1], x, content[2], content[3], content[4], content[5], content[6]);
    }

};

function position(shape, color, i, prefix, value, size, points, feedback) {

    // Set the shape/color of the right answer zone already placed
    if (shape == 'circle') {
        switch (color) {
        case 'black' :
            $('#img' + i).attr('src', prefix + 'circleblack.png');
            break;
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

        case 'brown' :
            $('#img' + i).attr('src', prefix + 'circlebrown.png');
            break;
        default :
            $('#img' + i).attr('src', prefix + 'circleblack.png');
            break;
        }

    } else if (shape == 'square') {
        switch (color) {
        case 'black' :
            $('#img' + i).attr('src', prefix + 'squareblack.png');
            break;
        case 'white' :
            $('#img' + i).attr('src', prefix + 'squarew.png');
            break;

        case 'red' :
            $('#img' + i).attr('src', prefix + 'squarer.png');
            break;

        case 'blue' :
            $('#img' + i).attr('src', prefix + 'squareb.png');
            break;

        case 'purple' :
            $('#img' + i).attr('src', prefix + 'squarep.png');
            break;

        case 'green' :
            $('#img' + i).attr('src', prefix + 'squareg.png');
            break;

        case 'orange' :
            $('#img' + i).attr('src', prefix + 'squareo.png');
            break;

        case 'yellow' :
            $('#img' + i).attr('src', prefix + 'squarey.png');
            break;

        case 'brown' :
            $('#img' + i).attr('src', prefix + 'squarebrown.png');
             break;
        default :
            $('#img' + i).attr('src', prefix + 'squareblack.png');
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

    $('#dragContainer' + grade).append('<p style="position: absolute; left: 5px; top: -20px;">'
        + parseInt(grade + 1) + '</p>');

    alreadyPlacedAnswersZoneEdit(shape, color, prefix, points,feedback);

    grade++;
    //Image center
  //  $('#Answer').children('div').css({'margin': 'auto'});
}

function alreadyPlacedAnswersZoneEdit(shape, color, pathImg, point, feedback) {


    var contenu = '<tr><td class="classic">' + (parseInt(grade) + 1) + '</td><td class="classic">';

    if (shape == 'square') {
        contenu += '<select class="form-control" id="shape' + grade + '" size="1" onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">\n\
                        <option value="circle">' + translations['tradCircle', {}, 'ujm_exo'] + '</option>\n\
                        <option value="square" selected>' + translations['tradSquare', {}, 'ujm_exo'] + '</option>\n\
                    </select></td>'
    } else {
        contenu += '<select class="form-control" id="shape' + grade + '" size="1" onchange="alterAlreadyPlaced(\'' + pathImg + '\', this);">\n\
                        <option value="circle" selected>' + translations['tradCircle', {}, 'ujm_exo'] + '</option>\n\
                        <option value="square">' + translations['tradSquare', {}, 'ujm_exo'] + '</option>\n\
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
                <option value="brown"    style="background-color:#5A4C41;"> &nbsp;&nbsp;&nbsp; </option>\n\
             </select></td>';

    contenu += '<td class="classic"><input class="form-control" type="TEXT" id="points' + grade + '" style="width:100px;display:block; margin:auto;" value="'
                    + point + '" onblur="changePoints(\'' + translations['tradWrongPoint'] + '\', this);"></td>\n\
                    <td class="classic" id="row_feedback_'+grade+'"><a class="btn btn-default" id="btn_feedback_'+grade+'" onClick="addFeedbackGraphic('+grade+',\'btn_feedback_'+grade+'\',\'row_feedback_'+grade+'\');"><i class="fa fa-comments-o"></i></a></td>\n\
                    <td class="classic"><a class="btn btn-danger" id="delete'+grade+'"><i class="fa fa-close"></i></a></td></tr>';
            $('#AlreadyPlacedArray').find('tbody').append(contenu);
    //Button delete
        $('#delete'+grade).click(function(e) {
        $(this).parent('td').parent('tr').remove();
        var chiffre = $(this).attr('id').replace('delete', '');
        $('#dragContainer'+chiffre).remove();
        setOrderAfterDel();
        e.preventDefault();
    });
    $('#row_feedback_'+grade).each(function() {
        if(feedback !== "")
        {
            addFeedbackGraphic(grade,'btn_feedback_'+grade,'row_feedback_'+grade);             
            $('#ujm_exobundle_interactiongraphictype_coords_'+grade+'_feedback').val(feedback);
            textareaAdvancedEdition();
        }
    });
    //Displays the array anwser
    $('#AlreadyPlacedArray').css({"display" : "inline"});
}
