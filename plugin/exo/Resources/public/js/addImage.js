var button = $('#uploadSubmit'); // The submit button to hide it or not when uploading
var list = $('#Result'); // The uploading text when submit button is hide
var allowclose = true; // Allow the pop up to be closed
var inputselected = false; // If the input is selected, the pop up cannot be closed

// Display or not the button to upload
list.css({"display" : "none"});
button.css({"display" : "inline-block"});

// Display the loading message
function DisplayMessage() {
    list.css({"display" : "inline-block"});
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
