window.onload = function () {

    if (document.getElementById('info')) {
        var infos = document.getElementById('info').value;
        var i = infos.substr(0, infos.indexOf('~'));
        infos = infos.substr(infos.indexOf('~') + 1);
        var info = infos.split('^');

        for (var x = 0 ; x < i ; x++) {
            var content = info[x].split(';');
            whichImg(content[0], content[1], x, content[3], content[4], content[5], content[6], content[2], content[7], 1);
        }
    }
};

function whichImg(shape, color, i, x, y, rx, ry, prefix, size, id) {

    // Set the shape/color of the answer zone
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circlew.png';
            break;

        case 'red' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circler.png';
            break;

        case 'blue' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circleb.png';
            break;

        case 'purple' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circlep.png';
            break;

        case 'green' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circleg.png';
            break;

        case 'orange' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circleo.png';
            break;

        case 'yellow' :
            document.getElementById(id + 'ra' + i).src = prefix + 'circley.png';
            break;

        default :
            document.getElementById(id + 'ra' + i).src = prefix + 'circlew.png';
            break;
        }

    } else if (shape == 'rectangle') {
        switch (color) {
        case 'white' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectanglew.jpg';
            break;

        case 'red' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectangler.jpg';
            break;

        case 'blue' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectangleb.jpg';
            break;

        case 'purple' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectanglep.jpg';
            break;

        case 'green' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectangleg.jpg';
            break;

        case 'orange' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectangleo.jpg';
            break;

        case 'yellow' :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectangley.jpg';
            break;

        default :
            document.getElementById(id + 'ra' + i).src = prefix + 'rectanglew.jpg';
        }
    }

    document.getElementById(id + 'ra' + i).width = size;

    // Place the right answer zones
    document.getElementById(id + 'ra' + i).style.left = String(parseInt(x) - (size / 2)) + 'px';
    document.getElementById(id + 'ra' + i).style.top = String(parseInt(y) - (size / 2)) + 'px';

    // Place student answer zones if defined
    if (rx != 'a' && ry != 'a' && rx != '' && ry != '') {
        document.getElementById(id + 'cursor' + i).style.left = String(parseInt(rx) - 10) + 'px';
        document.getElementById(id + 'cursor' + i).style.top = String(parseInt(ry) - 10) + 'px';
    } else { // Else don't display the unplaced answer zone of the student
        document.getElementById(id + 'cursor' + i).style.visibility = 'hidden';
    }
}