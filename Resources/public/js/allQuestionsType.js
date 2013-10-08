// Hide description and feeback by default for more visibility
$('#descriptionOptional').css({"display" : "none"});
$('#feedbackOptional').css({"display" : "none"});
$('#descriptionOptionalShow').css({"display" : "inline-block"});
$('#feebackOptionalShow').css({"display" : "inline-block"});
$('#descriptionOptionalHide').css({"display" : "none"});
$('#feebackOptionalHide').css({"display" : "none"});

$("*[id$='_interaction_question_model']").attr("disabled", true);

// Display the textarea
function DisplayOptional(type) {
    if (type == 'feedback') {
        $('#feebackOptionalShow').css({"display" : "none"});
        $('#feebackOptionalHide').css({"display" : "inline-block"});
        $('#feedbackOptional').css({"display" : "inline-block"});
    }

    if (type == 'description') {
        $('#descriptionOptionalShow').css({"display" : "none"});
        $('#descriptionOptionalHide').css({"display" : "inline-block"});
        $('#descriptionOptional').css({"display" : "inline-block"});
    }
}

// Hide the textarea
function HideOptional(type) {
    if (type == 'feedback') {
        $('#feebackOptionalShow').css({"display" : "inline-block"});
        $('#feebackOptionalHide').css({"display" : "none"});
        $('#feedbackOptional').css({"display" : "none"});
    }

    if (type == 'description') {
        $('#descriptionOptionalShow').css({"display" : "inline-block"});
        $('#descriptionOptionalHide').css({"display" : "none"});
        $('#descriptionOptional').css({"display" : "none"});
    }
}

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
                $('#linkedCategory').css({"display" : "inline-block"});
                break;
            } else {
                $('#linkedCategory').css({"display" : "none"});
                 break;
            }
        } else {
            $('#linkedCategory').css({"display" : "inline-block"});
        }
    }
}

displayDeleteCategory();