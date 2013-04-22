function whichImg(shape, color, i, x, y, rx, ry, prefix,size) {
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            document.getElementById('ra' + i).src = prefix + 'circlew.png';
            break;

        case 'red' :
            document.getElementById('ra' + i).src = prefix + 'circler.png';
            break;

        case 'blue' :
            document.getElementById('ra' + i).src = prefix + 'circleb.png';
            break;

        case 'purple' :
            document.getElementById('ra' + i).src = prefix + 'circlep.png';
            break;

        case 'green' :
            document.getElementById('ra' + i).src = prefix + 'circleg.png';
            break;

        case 'orange' :
            document.getElementById('ra' + i).src = prefix + 'circleo.png';
            break;

        case 'yellow' :
            document.getElementById('ra' + i).src = prefix + 'circley.png';
            break;

        default :
            document.getElementById('ra' + i).src = prefix + 'circlew.png';
            break;
        }

    } else if (shape == 'rectangle') {
        switch (color) {
        case 'white' :
            document.getElementById('ra' + i).src = prefix + 'rectanglew.jpg';
            break;

        case 'red' :
            document.getElementById('ra' + i).src = prefix + 'rectangler.jpg';
            break;

        case 'blue' :
            document.getElementById('ra' + i).src = prefix + 'rectangleb.jpg';
            break;

        case 'purple' :
            document.getElementById('ra' + i).src = prefix + 'rectanglep.jpg';
            break;

        case 'green' :
            document.getElementById('ra' + i).src = prefix + 'rectangleg.jpg';
            break;

        case 'orange' :
            document.getElementById('ra' + i).src = prefix + 'rectangleo.jpg';
            break;

        case 'yellow' :
            document.getElementById('ra' + i).src = prefix + 'rectangley.jpg';
            break;

        default :
            document.getElementById('ra' + i).src = prefix + 'rectanglew.jpg';
        }
    }
    
    
    document.getElementById('ra' + i).style.left = String(document.getElementById('AnswerImage').offsetLeft +
        parseInt(x) - (size/2) - 10) + 'px';
    document.getElementById('ra' + i).style.top = String(document.getElementById('AnswerImage').offsetTop +
        parseInt(y) - (size/2)) + 'px';
    
    if(rx != 'a' && ry != 'a'){
        document.getElementById('cursor' + i).style.left = String(document.getElementById('AnswerImage').offsetLeft +
            parseInt(rx) - 20) + 'px';
        document.getElementById('cursor' + i).style.top = String(document.getElementById('AnswerImage').offsetTop +
            parseInt(ry) - 10) + 'px';
    } else {
        document.getElementById('cursor' + i).style.visibility = 'hidden';
    }
}