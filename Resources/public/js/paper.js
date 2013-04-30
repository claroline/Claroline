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

function recordGraph() { // Record the answer zones of the student before changing page or submit

    var taille = document.getElementById('nbpointer').value;

    for (var x = 1 ; x < taille ; x++) {
        var label = 'cursor' + x;

        if (!tempCoords[label]) {
            if (document.getElementById(label).style.top) {

                var n1 = document.getElementById(label).style.left;
                var n2 = document.getElementById('AnswerImg').offsetLeft;
                var n3 = document.getElementById(label).style.top;
                var n4 = document.getElementById('AnswerImg').offsetTop;

                var abs = n1.substring(0, n1.indexOf('px')) - n2 + 10;
                var ord = n3.substring(0, n3.indexOf('px')) - n4 + 10;

                tempCoords[label] = abs + '-' + ord;
            } else {
                tempCoords[label] = 'a-a'; // If student didn't placed all the answer zones
            }
        }
        document.getElementById('answers').value += tempCoords[label] + ';';
    }
}

function displayAnswersGraph(response) { // Place the already placed answer zones

    var taille = document.getElementById('nbpointer').value;
    var coords = response.split(';');

    for (var x = 1 ; x < taille ; x++) {
        var xy = coords[x - 1].split('-');
        var cur = 'cursor' + x;

        document.getElementById(cur).style.left = String(parseInt(document.getElementById('AnswerImg').offsetLeft) +
            parseInt(xy[0]) - 20) + 'px';
        document.getElementById(cur).style.top = String(parseInt(document.getElementById('AnswerImg').offsetTop) +
            parseInt(xy[1]) - 10) + 'px';
    }
}