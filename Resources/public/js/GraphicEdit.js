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

    } else if (shape == 'rectangle') {
        switch (color) {
        case 'white' :
            $('#img' + i).attr('src', prefix + 'rectanglew.jpg');
            break;

        case 'red' :
            $('#img' + i).attr('src', prefix + 'rectangler.jpg');
            break;

        case 'blue' :
            $('#img' + i).attr('src', prefix + 'rectangleb.jpg');
            break;

        case 'purple' :
            $('#img' + i).attr('src', prefix + 'rectanglep.jpg');
            break;

        case 'green' :
            $('#img' + i).attr('src', prefix + 'rectangleg.jpg');
            break;

        case 'orange' :
            $('#img' + i).attr('src', prefix + 'rectangleo.jpg');
            break;

        case 'yellow' :
            $('#img' + i).attr('src', prefix + 'rectangley.jpg');
            break;

        default :
            $('#img' + i).attr('src', prefix + 'rectanglew.jpg');
        }
    }

    // Set the width of the right answer zone already placed
    $('#img' + i).attr('width', size);

    // Set the position of the right answer zone already placed
    var x = value.substr(0, value.indexOf(','));
    var y = value.substr(value.indexOf(',') + 1);

    $('#img' + i).css({
        "left" : String(parseInt(x) - (size / 2)) + 'px',
        "top"  : String(parseInt(y) - (size / 2)) + 'px'
    });

    // Set the id of the right answer zone already placed
    grade++;
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
    })
    .parent()
    .draggable({
        containment : '#AnswerImage',
        cursor : 'move',

        stop: function(event, ui) {
            $('#img' + i).css("left", $(this).css("left"));
            $('#img' + i).css("top", $(this).css("top"));
        }
    });
}