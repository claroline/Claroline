$('#agenda_form_start').on('click', function (date) {
    $('#agenda_form_start').datepicker('show');
});

  $('#agenda_form_start').on('changeDate', function () {
        $('#agenda_form_start').datepicker('hide');
});