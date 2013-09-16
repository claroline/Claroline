var button = $('#uploadSubmit'); // The submit button to hide it or not when uploading
var list = $('#Result'); // The uploading text when submit button is hide
var allowclose = true; // Allow the pop up to be closed
var inputselected = false; // If the input is selected, the pop up cannot be closed

// Display or not the button to upload
list.css({"display" : "none"});
button.css({"display" : "block"});


// Resize the pop up to show all the component
window.onload = function () {
    window.resizeTo(
        $('#picture').offset().left + $('#picture').width() + 50,
        $('#uploadSubmit').offset().top + button.height() + 100
    );
};

// Put the new image into the drop-down list
function ChangeList(idDoc, label, type, NotImageMesssage) {

    list.css({"display" : "none"});
    button.css({"display" : "block"});

    if (type == '.png' || type == '.jpeg' || type == '.jpg' || type == '.gif' || type == '.bmp') {

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
    list.css({"display" : "block"});
    button.css({"display" : "none"});
}

// To check if the label of the image is valid and doesn't already exist before submit
function ValidName(message, label, path, messageA, form) {
    var correctName = false;
    var uniqueName = false;

    if (/^[a-z0-9_ !?éèà]+$/gi.test($('#' + label).val()) == false) {
        alert(message);
        $('#' + label).focus();
        return false;
    } else {
        correctName = true;
    }

    $.ajax({
        type: 'POST',
        url: path,
        data: {
            label : $('#' + label).val()
        },
        cache: false,
        success: function (data) {
            if (data == 'already') {
                alert(messageA);
                $('#' + label).focus();
                return false;
            } else {
                uniqueName = true;
            }

            if (correctName === true && uniqueName === true) {
                $('#' + form).submit();
            }
        }
    });
}