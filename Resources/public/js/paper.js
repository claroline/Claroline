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

function submitForm(numQuestionToDisplayed, interactionType) {
    document.getElementById('numQuestionToDisplayed').value = numQuestionToDisplayed;

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    document.getElementById('formResponse').submit();
}

function finish(interactionType) {
    document.getElementById('numQuestionToDisplayed').value = 'finish';

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    document.getElementById('formResponse').submit();
}

function interupt(interactionType) {
    document.getElementById('numQuestionToDisplayed').value = 'interupt';

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    document.getElementById('formResponse').submit();
}

function recordGraph() {

    var taille = document.getElementById('nbpointer').value;

    for (var x = 1 ; x < taille ; x++) {
        var label = 'cursor' + x;

        if (!tempCoords[label]) {
            tempCoords[label] = 'a-a';
        }
    }

    for (var cur in tempCoords) {
        document.getElementById('answers').value += tempCoords[cur] + ';';
    }
}

function displayAnswersGraph(response) {
    //alert(response);
}