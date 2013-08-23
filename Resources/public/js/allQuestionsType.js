document.getElementById('descriptionOptional').style.display = 'none';
document.getElementById('feedbackOptional').style.display = 'none';

document.getElementById('descriptionOptionalShow').style.display = 'block';
document.getElementById('feebackOptionalShow').style.display = 'block';

document.getElementById('descriptionOptionalHide').style.display = 'none';
document.getElementById('feebackOptionalHide').style.display = 'none';


function DisplayOptional(type) {
    if (type == 'feedback') {
        document.getElementById('feebackOptionalShow').style.display = 'none';
        document.getElementById('feebackOptionalHide').style.display = 'block';
        document.getElementById('feedbackOptional').style.display = 'block';
    }
    
    if (type == 'description') {
        document.getElementById('descriptionOptionalShow').style.display = 'none';
        document.getElementById('descriptionOptionalHide').style.display = 'block';
        document.getElementById('descriptionOptional').style.display = 'block';
    }
}

function HideOptional(type) {
    if (type == 'feedback') {
        document.getElementById('feebackOptionalShow').style.display = 'block';
        document.getElementById('feebackOptionalHide').style.display = 'none';
        document.getElementById('feedbackOptional').style.display = 'none';
    }
    
    if (type == 'description') {
        document.getElementById('descriptionOptionalShow').style.display = 'block';
        document.getElementById('descriptionOptionalHide').style.display = 'none';
        document.getElementById('descriptionOptional').style.display = 'none';
    }
}