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


