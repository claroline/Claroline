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
    document.getElementById("numQuestionToDisplayed").value = numQuestionToDisplayed;
    
    if (interactionType == 'InteractionGraphic') {
        recordGraph('eza','vwx');
    }
    
    document.getElementById("formResponse").submit();
}

function finish(interactionType, interactionType) {
    document.getElementById("numQuestionToDisplayed").value = 'finish';
    
    if (interactionType == 'InteractionGraphic') {
        recordGraph('eza','vwx');
    }
    
    document.getElementById("formResponse").submit();
}

function interupt(interactionType) {
    document.getElementById("numQuestionToDisplayed").value = 'interupt';
    
    if (interactionType == 'InteractionGraphic') {
        recordGraph('eza','vwx');
    }
    
    document.getElementById("formResponse").submit();
}

function recordGraph(noAnswerZone, notAll) {

    var item = getTaille(tempCoords);

    if (item == 0) {
        alert(noAnswerZone);
        return false;
    } else if (item < (taille - 1)) {
        alert(notAll);
        return false;
    } else {
        for (var cur in tempCoords) {
            document.getElementById('answers').value += tempCoords[cur] + ';';
        }
    }
}

function displayAnswersGraph(response) {
    alert(response);
}