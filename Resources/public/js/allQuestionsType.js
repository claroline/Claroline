// Hide description and feeback by default for more visibility
$('#descriptionOptional').css({"display" : "none"});
$('#feedbackOptional').css({"display" : "none"});
$('#descriptionOptionalShow').css({"display" : "block"});
$('#feebackOptionalShow').css({"display" : "block"});
$('#descriptionOptionalHide').css({"display" : "none"});
$('#feebackOptionalHide').css({"display" : "none"});

// Display the textarea
function DisplayOptional(type) {
    if (type == 'feedback') {
        $('#feebackOptionalShow').css({"display" : "none"});
        $('#feebackOptionalHide').css({"display" : "block"});
        $('#feedbackOptional').css({"display" : "block"});
    }

    if (type == 'description') {
        $('#descriptionOptionalShow').css({"display" : "none"});
        $('#descriptionOptionalHide').css({"display" : "block"});
        $('#descriptionOptional').css({"display" : "block"});
    }
}

// Hide the textarea
function HideOptional(type) {
    if (type == 'feedback') {
        $('#feebackOptionalShow').css({"display" : "block"});
        $('#feebackOptionalHide').css({"display" : "none"});
        $('#feedbackOptional').css({"display" : "none"});
    }

    if (type == 'description') {
        $('#descriptionOptionalShow').css({"display" : "block"});
        $('#descriptionOptionalHide').css({"display" : "none"});
        $('#descriptionOptional').css({"display" : "none"});
    }
}

// Show pop up to alter category label
function show() {
    $('#alterCategory').css({ "display" : "block" });
}

// Hide pop up to alter category label
function hide() {
    $('#alterCategory').css({ "display" : "none" });
}

// Change the name of the category
$("#updateSubmit").click(function () {
    var idOldCategory = $("*[id$='_interaction_question_category']").val(); // Id of the category to update
    var newlabel = $('#newlabel').val(); // New label of the category
    var path = $('#pathAlter').val(); // Path to the controller

    // If new label is empty
    if (newlabel == '') {
        alert(translations['wrongName']);
        return false;
    } else {
        // If new label already exist
        var exists = false;
        $("*[id$='_interaction_question_category']  option").each(function () {
            if (this.text == newlabel) {
                exists = true;
                return false;
            }
        });

        if (exists) {
           alert(translations['alreadyExist']);
        } else {
            $.ajax({
                type: "POST",
                url: path,
                data: {
                    newlabel: newlabel,
                    idOldCategory: idOldCategory
                },
                cache: false,
                success: function (data) {
                    // Remove the old label from the list
                    $("*[id$='_interaction_question_category'] option[value=\""+idOldCategory+"\"]").remove();

                    // Add the new one to the list
                    $("*[id$='_interaction_question_category']")
                        .append($('<option selected="selected" value="'+data+'"></option>')
                        .text(newlabel));

                    // Hide the pop up to update the name
                    hide();
                }
            });
        }
    }
});

// Delete the name of the category
function dropCategory() {
    var idCategory = $("*[id$='_interaction_question_category']").val(); // Id of the category to delete
    var path = $('#pathDrop').val(); // Path to the controller

    $.ajax({
        type: "POST",
        url: path,
        data: {
            idCategory: idCategory
        },
        cache: false,
        success: function (data) {
            // Remove the label from the list
            $("*[id$='_interaction_question_category'] option[value=\""+idCategory+"\"]").remove();
            displayDeleteCategory();
        }
    });
}

var allCategory = $('#categoryArray').val();
var categoryArray = allCategory.split(';');

$("*[id$='_interaction_question_category']").change(function () {
    displayDeleteCategory();
});

function displayDeleteCategory() {
    var idCat = $("*[id$='_interaction_question_category']").val();


    for(var i = 0 ; i < categoryArray.length - 1 ; i++) {
        var index = categoryArray[i].substring(0, categoryArray[i].indexOf('/'));
        var contain = categoryArray[i].substring(categoryArray[i].indexOf('/') + 1);

        if (idCat == index) {
            if (contain == 0) {
                $('#linkedCategory').css({"display" : "block"});
                break;
            } else {
                $('#linkedCategory').css({"display" : "none"});
                 break;
            }
        } else {
            $('#linkedCategory').css({"display" : "block"});
        }
    }
}

displayDeleteCategory();