window.onload = function () {

    var infos = document.getElementById('info').value;
    var i = infos.substr(0, infos.indexOf('~'));
    infos = infos.substr(infos.indexOf('~') + 1);
    var info = infos.split('^');

    for (var x = 0 ; x < i ; x++) {
        var content = info[x].split(';');
        position(content[0], content[1], x, content[2], content[3], content[4], content[5]);
    }
};

function position(shape, color, i, prefix, value, size, points) {

    // Set the shape/color of the answer zone
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            document.getElementById('img' + i).src = prefix + 'circlew.png';
            break;

        case 'red' :
            document.getElementById('img' + i).src = prefix + 'circler.png';
            break;

        case 'blue' :
            document.getElementById('img' + i).src = prefix + 'circleb.png';
            break;

        case 'purple' :
            document.getElementById('img' + i).src = prefix + 'circlep.png';
            break;

        case 'green' :
            document.getElementById('img' + i).src = prefix + 'circleg.png';
            break;

        case 'orange' :
            document.getElementById('img' + i).src = prefix + 'circleo.png';
            break;

        case 'yellow' :
            document.getElementById('img' + i).src = prefix + 'circley.png';
            break;

        default :
            document.getElementById('img' + i).src = prefix + 'circlew.png';
            break;
        }

    } else if (shape == 'rectangle') {
        switch (color) {
        case 'white' :
            document.getElementById('img' + i).src = prefix + 'rectanglew.jpg';
            break;

        case 'red' :
            document.getElementById('img' + i).src = prefix + 'rectangler.jpg';
            break;

        case 'blue' :
            document.getElementById('img' + i).src = prefix + 'rectangleb.jpg';
            break;

        case 'purple' :
            document.getElementById('img' + i).src = prefix + 'rectanglep.jpg';
            break;

        case 'green' :
            document.getElementById('img' + i).src = prefix + 'rectangleg.jpg';
            break;

        case 'orange' :
            document.getElementById('img' + i).src = prefix + 'rectangleo.jpg';
            break;

        case 'yellow' :
            document.getElementById('img' + i).src = prefix + 'rectangley.jpg';
            break;

        default :
            document.getElementById('img' + i).src = prefix + 'rectanglew.jpg';
        }
    }

    var x = value.substr(0, value.indexOf(','));
    var y = value.substr(value.indexOf(',') + 1);

    document.getElementById('img' + i).width = size;

    document.getElementById('img' + i).style.left = String(parseInt(x) - (size / 2)) + 'px';
    document.getElementById('img' + i).style.top = String(parseInt(y) - (size / 2)) + 'px';

    grade++;
    var name = 'img' + i;
    point[name] = points;
}