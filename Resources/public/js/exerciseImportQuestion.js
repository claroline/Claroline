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

function importQuestion(pathImport, exoID, pageToGo, nothingToImport) {
    if (pos == 0) {
        alert(nothingToImport);
    } else {
        $.ajax({
            type: 'POST',
            url: pathImport,
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
