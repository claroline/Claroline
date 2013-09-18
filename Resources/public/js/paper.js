function show_hint(idHint, path_hint_show, confirm_hint, nbr_hint, paper) {
    //"use strict";
    
    if (confirm(confirm_hint)) {
        show_hint2(idHint, path_hint_show, nbr_hint, paper);
    }
}

function show_hint2(idHint, path_hint_show, nbr_hint, paper) {
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
}

function submitForm(numQuestionToDisplayed, interactionType) {
    $('#numQuestionToDisplayed').val(numQuestionToDisplayed);

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    $('#formResponse').submit();
}

function finish(interactionType, alert) {
    $('#numQuestionToDisplayed').val('finish');

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    if (confirm(alert)) {
        $('#formResponse').submit();
    }
}

function interupt(interactionType) {
    $('#numQuestionToDisplayed').val('interupt');

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    $('#formResponse').submit();
}

// Record the answer zones of the student before changing page or submit
function recordGraph() {

    var margin = $('#AnswerImg').css("margin-top"); // margin top of the answer image

    var taille = $('#nbpointer').val(); // number of total answer

    for (var x = 1 ; x < taille ; x++) {
        var label = 'cursor' + x;
        var top = $('#' + label).css("top");

        // If answer not already registered
        if (!tempCoords[label]) {
            // If answer is placed
            if (parseInt(top.substring(0, top.indexOf('px'))) > 2) {
                // Get coordonates of the answer
                var abs = parseInt($('#' + label).position().left) + 10;
                var ord = parseInt($('#' + label).position().top) - parseInt(margin) + 10;

                // And push into an array in order to save it
                tempCoords[label] = abs + '-' + ord;
            } else {
                // If answer not placed, push "a-a"
                tempCoords[label] = 'a-a'; // If student didn't placed all the answer zones
            }
        }
        // Put all the answers' coords to save it into the input
        $('#answers').val($('#answers').val() + tempCoords[label] + ';');
    }
}

// Place the already placed answer zones
function displayAnswersGraph(response) {

    // margin top of the answer image
    var marg = $('#AnswerImg').css("margin-top");

    // If answer zone has been placed, display it on the image
    if (response != 'empty') {
        var taille = $('#nbpointer').val();
        var coords = response.split(';');

        for (var x = 1 ; x < taille ; x++) {
            var xy = coords[x - 1].split('-');
            var cur = 'cursor' + x;

            $('#' + cur).css({
                "left" : String(parseInt(xy[0]) - 10) + 'px',
                "top"  : String(parseInt(xy[1]) + parseInt(marg) - 10) + 'px'
            });
        }
    }
}