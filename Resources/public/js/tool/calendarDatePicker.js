$('.datepicker').on('click', function (event) {
    var date = $(event.currentTarget).val();
    $(event.currentTarget).datepicker('show');
    //$('#agenda_form_start').datepicker("setDate", date);
});

  $('.datepicker').on('changeDate', function (event) {
        $(event.currentTarget).datepicker('hide');
});

function validateTime(strTime) {
    var regex = /^(20|21|22|23|[0-1][0-9]):[0-5][0-9]$/;
    if (regex.test(strTime)) {
       console.debug(strTime);
       return (strTime);
    } else {
        console.debug(strTime);
        return '00:00';
    }
}

$('.hours').on('focusout',function(event) {
    var rep = validateTime($(event.currentTarget).val());
    $(event.currentTarget).val(rep);
});