window.onload = function() {
    var infos = document.getElementById('info').value;
    var i = infos.substr(0, infos.indexOf('~'));
    infos = infos.substr(infos.indexOf('~') + 1);
    
    var info = infos.split('^');
    
    for (var x = 1 ; x < i ; x++) {
        var contenu = info[x - 1].split(';');
        position(contenu[0], contenu[1], x, contenu[2], contenu[3], contenu[4]);
    }
}

function position(shape, color, i, prefix, value, size) {

    // Set the shape/color of the answer zone
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            document.getElementById('cursor' + i).src = prefix + 'circlew.png';
            break;

        case 'red' :
            document.getElementById('cursor' + i).src = prefix + 'circler.png';
            break;

        case 'blue' :
            document.getElementById('cursor' + i).src = prefix + 'circleb.png';
            break;

        case 'purple' :
            document.getElementById('cursor' + i).src = prefix + 'circlep.png';
            break;

        case 'green' :
            document.getElementById('cursor' + i).src = prefix + 'circleg.png';
            break;

        case 'orange' :
            document.getElementById('cursor' + i).src = prefix + 'circleo.png';
            break;

        case 'yellow' :
            document.getElementById('cursor' + i).src = prefix + 'circley.png';
            break;

        default :
            document.getElementById('cursor' + i).src = prefix + 'circlew.png';
            break;
        }

    } else if (shape == 'rectangle') {
        switch (color) {
        case 'white' :
            document.getElementById('cursor' + i).src = prefix + 'rectanglew.jpg';
            break;

        case 'red' :
            document.getElementById('cursor' + i).src = prefix + 'rectangler.jpg';
            break;

        case 'blue' :
            document.getElementById('cursor' + i).src = prefix + 'rectangleb.jpg';
            break;

        case 'purple' :
            document.getElementById('cursor' + i).src = prefix + 'rectanglep.jpg';
            break;

        case 'green' :
            document.getElementById('cursor' + i).src = prefix + 'rectangleg.jpg';
            break;

        case 'orange' :
            document.getElementById('cursor' + i).src = prefix + 'rectangleo.jpg';
            break;

        case 'yellow' :
            document.getElementById('cursor' + i).src = prefix + 'rectangley.jpg';
            break;

        default :
            document.getElementById('cursor' + i).src = prefix + 'rectanglew.jpg';
        }
    }

    var x = value.substr(0, value.indexOf(','));
    var y = value.substr(value.indexOf(',') + 1);

    // Place the answer zones
    document.getElementById('cursor' + i).style.left = String(parseInt(document.getElementById('AnswerImage').offsetLeft) +
        parseInt(x) - (size / 2)) + 'px';
    document.getElementById('cursor' + i).style.top = String(document.getElementById('AnswerImage').offsetTop +
        parseInt(y) - (size / 2)) + 'px';
}