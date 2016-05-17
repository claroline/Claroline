$(document).ready(function() {
    if ($('#ujm_exo_exercise_nbQuestion').val() == 0) {
        $('#ujm_exo_exercise_keepSameQuestion').attr('disabled', true);
    }

    $("#ujm_exobundle_exercisetype_nbQuestion").keyup(function () {
        if ($('#ujm_exobundle_exercisetype_nbQuestion').val() == 0) {
            $('#ujm_exo_exercise_keepSameQuestion').removeAttr('checked');
            $('#ujm_exo_exercise_keepSameQuestion').attr('disabled', true);
        } else {
            $('#ujm_exo_exercise_keepSameQuestion').attr('disabled', false);
        }
    });
});
