function whichImg(shape, color, i, x, y, rx, ry) {
    if (shape == 'circle') {
        switch (color) {
        case 'white' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circlew.png';
            break;

        case 'red' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circler.png';
            break;

        case 'blue' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circleb.png';
            break;

        case 'purple' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circlep.png';
            break;

        case 'green' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circleg.png';
            break;

        case 'orange' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circleo.png';
            break;

        case 'yellow' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circley.png';
            break;

        default :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/circlew.png';
            break;
        }

    } else if (shape == 'rectangle') {
        switch (color) {
        case 'white' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectanglew.jpg';
            break;

        case 'red' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectangler.jpg';
            break;

        case 'blue' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectangleb.jpg';
            break;

        case 'purple' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectanglep.jpg';
            break;

        case 'green' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectangleg.jpg';
            break;

        case 'orange' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectangleo.jpg';
            break;

        case 'yellow' :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectangley.jpg';
            break;

        default :
            document.getElementById('ra' + i).src = '/Claroline/web/bundles/ujmexo/images/graphic/rectanglew.jpg';
        }
    }
    
    document.getElementById('ra' + i).style.left = String(document.getElementById('AnswerImage').offsetLeft + x - 17) + 'px';
    document.getElementById('ra' + i).style.top = String(document.getElementById('AnswerImage').offsetTop + y - 10) + 'px';

    document.getElementById('cursor' + i).style.left = String(document.getElementById('AnswerImage').offsetLeft + rx - 15) + 'px';
    document.getElementById('cursor' + i).style.top = String(document.getElementById('AnswerImage').offsetTop + ry - 7) + 'px';
}