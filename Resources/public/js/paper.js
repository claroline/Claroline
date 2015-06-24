var validate = false;

function show_hint(idHint, path_hint_show, confirm_hint, nbr_hint, penalty, paper) {
    //"use strict";

    if (penalty > 0) {

        confirm_hint = confirm_hint.replace('X', String(penalty));

        if (confirm(confirm_hint)) {
            show_hint2(idHint, path_hint_show, nbr_hint, penalty, paper);
        }
    } else {
        show_hint2(idHint, path_hint_show, nbr_hint, penalty, paper);
    }
}

function show_hint2(idHint, path_hint_show, nbr_hint, penalty, paper) {
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
    validate = true;
    interType = interactionType;
    $('#numQuestionToDisplayed').val(numQuestionToDisplayed);

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    $('#formResponse').submit();
}

function finish(interactionType) {
    validate = true;
    interType = interactionType;
    $('#numQuestionToDisplayed').val('finish');

    if (interactionType == 'InteractionGraphic') {
        recordGraph();
    }

    $('#confirm-finish-exercise').modal('show');
}

function interupt(interactionType) {
    interType = interactionType;
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
                var abs = parseInt($('#' + label).position().left);
                var ord = parseInt($('#' + label).position().top) - parseInt(margin);

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
    var taille = $('#nbpointer').val();

    // If answer zone has been placed, display it on the image
    if (response != 'empty') {
        var coords = response.split(';');

        for (var x = 1 ; x < taille ; x++) {
            var xy = coords[x - 1].split('-');
            var cur = 'cursor' + x;

            $('#' + cur).css({
                "left" : String(parseInt(xy[0])) + 'px',
                "top" : String(parseInt(xy[1]) + parseInt(marg)) + 'px'
            });
        }
    } else {
        for (var h = 1 ; h < taille ; h++) {
            cur = 'cursor' + h;

            $("#" + cur).css({
                "left" : String(h * 30) + 'px',
                "top"  : '0px'
            });
        }
    }
}

function responseHole(response) {
    var tab = JSON.parse(response);

    $('#yAnswer').find('.blank').each(function(){
        id = $(this).attr('id');
        $('#' + id).attr('id', 'yAnswer_' + id);
    });

    $.each(tab, function (key, value) {
        if ($("#yAnswer_" + key).is("input")) {
            $("#yAnswer_" + key).val(value);
            $("#yAnswer_" + key).attr('readonly', true);
        } else {
            option = '<option>' + value + '</option>';
            $("#yAnswer_" + key).find('option:first').remove();
            $("#yAnswer_" + key).append(option);
        }
        if (value == $('#' + key).val()) {
           $("#yAnswer_" + key).css("color", "#00E900");
        } else {
            $("#yAnswer_" + key).css("color", "#F30000");
        }
        $("#" + key).css("color", "#2289B5");
        $("#" + key).attr('readonly', true);
    });
}

function paperResponseHole(response) {
    var tab = $.parseJSON(response);

    $('#interHoleResponse').find('.blank').each(function () {
        key = $(this).attr('id');
        if ($(this).is("input")) {
            $(this).val(tab[key]);
        } else {
            $('#' + key).find('*').filter(function () {
                return $(this).text() === tab[key];
            }).attr('selected', 'selected');
        }
    });
}

$(document).ready(function() {

    if ( (typeof allowToInterrupt !== 'undefined') && (!allowToInterrupt) ) {
        $(window).bind("beforeunload",function(){
            if (validate === false) {
                return mssg;
            } else {
                validate = false;
            }
        });

        $(window).bind("unload",function() {
            $('#numQuestionToDisplayed').val('finish');
            if (interType == 'InteractionGraphic') {
                recordGraph();
            }
            $('#formResponse').submit();
        });
    }
});
