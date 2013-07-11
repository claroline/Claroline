var button = document.getElementById('uploadSubmit'); // The submit button to hide it or not when uploading
var list = document.getElementById('Result'); // The uploading text when submit button is hide

var allowclose = true; // Allow the pop up to be closed
var inputselected = false; // If the input is selected, the pop up cannot be closed

// Display or not the button to upload
list.style.display = "none";
button.style.display = "block";


// Resize the pop up to show all the component
window.onload = function () {
    window.resizeTo(
        document.getElementById('picture').offsetLeft + document.getElementById('picture').width + 50,
        document.getElementById('uploadSubmit').offsetTop + document.getElementById('uploadSubmit').height + 100
    );
};

// Put the new image into the drop-down list
function ChangeList(idDoc, label, type, NotImageMesssage) {

    list.style.display = "none";
    button.style.display = "block";

    if (type == '.png' || type == '.jpeg' || type == '.jpg' || type == '.gif' || type =='.bmp') {
        this_select = window.opener.InterGraphForm.ujm_exobundle_interactiongraphictype_document;
        this_select.options[this_select.length] = new Option(label, idDoc, true, true);

        for (var i = 0; i < this_select.options.length; i++) {
            if (this_select.options[i].value == idDoc) {
                this_select.options[i].selected = true;
            }
        }

        window.close();
    } else {
        alert(NotImageMesssage);
    }
}

// Display the loading message
function DisplayMessage() {
    list.style.display = "block";
    button.style.display = "none";
}

// To check if the label of the image is valid
function ValidName(message, label, path, messageA) {
    var correctName = false;
    var uniqueName = false;

    if (/^[a-z0-9_ !?éèà]+$/gi.test(document.getElementById(label).value) == false) {
        alert(message);
        document.getElementById(label).focus();
        return false;
    } else {
        correctName = true;
    }

    $.ajax({
        type: 'POST',
        url: path,
        data: {
            label : document.getElementById(label).value
        },
        cache: false,
        success: function (data) {
            if (data == 'already') {
                alert(messageA);
                document.getElementById(label).focus();
                return false;
            } else {
                uniqueName = true;
            }
        }
    });

    if (correctName === true && uniqueName === true) {
        document.getElementById('uploadForm').submit();
    }
}

// --> If activate followed functions, can not move or resize the pop up ...
//// To close the window if its loose focus
//window.onblur = function () {
//    if (allowclose == true) {
//        window.close();
//    }
//};
//
//// If input file selected, cannot close the pop up
//document.getElementById('picture').onblur = function () {
//    allowclose = false;
//    inputselected = true;
//};
//
//// If input file unselected, can close the pop up
//window.onclick = function () {
//    if (inputselected == true) {
//        allowclose = true;
//    }
//};