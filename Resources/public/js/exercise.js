$(document).ready(function() {
    if($('#ujm_exobundle_exercisetype_nbQuestion').val() == 0) {
        $('#ujm_exobundle_exercisetype_keepSameQuestion').attr("disabled", true);
    }

    $("#ujm_exobundle_exercisetype_nbQuestion").keyup(function(){
        if($('#ujm_exobundle_exercisetype_nbQuestion').val() == 0) {
            $('#ujm_exobundle_exercisetype_keepSameQuestion').removeAttr('checked');
            $('#ujm_exobundle_exercisetype_keepSameQuestion').attr("disabled", true);
        } else {
            $('#ujm_exobundle_exercisetype_keepSameQuestion').attr("disabled", false);
        }
    });
});

function publish(url, exerciseId) {
    var id = exerciseId;

    $.ajax({
            type: "POST",
            url: url,
            data: { exerciseId: id },
            cache: false,
            success: function (data) {
                hideBtPublish ();
                if (data == 0) {
                    displayBtUnpublish ();
                }
            }
        });
}

function unpublish(url, exerciseId) {
    var id = exerciseId;

    $.ajax({
            type: "POST",
            url: url,
            data: { exerciseId: id },
            cache: false,
            success: function (data) {
                hideBtUnpublish ();
                displayBtPublish ();
            }
        });
}

function displayBtPublish () {
    $("#divPublish").css("display", "block");
}

function hideBtPublish () {
    $("#divPublish").css("display", "none");
}

function displayBtUnpublish () {
    $("#spanUnpublish").css("display", "inline");
}

function hideBtUnpublish () {
    $("#spanUnpublish").css("display", "none");
}
