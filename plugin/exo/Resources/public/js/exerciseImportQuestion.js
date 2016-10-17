$(document).ready(function () {
    $('[name=my]').removeAttr("disabled");
});

function addQuestionInOrder(idQ) {
    var position = questionId.indexOf(idQ);

    if (position != -1) {
        questionId.splice(position, 1);
    } else {
        questionId[pos] = idQ;
        pos++;
    }
}

function importQuestion(exoID, pageToGo) {
    if (pos == 0) {
        alert(Translator.trans('no_thing_import', {}, 'ujm_exo'))
    } else {
        $.ajax({
            type: 'POST',
            url: Routing.generate('ujm_exercise_validate_import'),
            data: {
                exoID : exoID,
                pageGoNow : pageToGo,
                qid: questionId
            },
            cache: false,
            success: function (data) {
                window.location.href = data;
            }
        });
    }
}
