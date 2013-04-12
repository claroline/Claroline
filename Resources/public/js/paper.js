function show_hint(idHint, path_hint_show, confirm_hint, nbr_hint, paper) {
    //"use strict";
    if (confirm(confirm_hint)) {
        $.ajax({
            type: "POST",
            url: path_hint_show,
            data: {
                id: idHint,
                paper: paper
            },
            cache: false,
            success: function (data) {
                $("#div_hint" + nbr_hint).html(data);
            }
        });
    } else {
        // return fales;
    }
}

function submitForm(numQuestionToDisplayed) {
    document.getElementById("numQuestionToDisplayed").value = numQuestionToDisplayed;
    document.getElementById("formResponse").submit();
}

function finish() {
    document.getElementById("numQuestionToDisplayed").value = 'finish';
    document.getElementById("formResponse").submit();
}

function interupt() {
    document.getElementById("numQuestionToDisplayed").value = 'interupt';
    document.getElementById("formResponse").submit();
}