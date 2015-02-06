
function validateTime(strTime) {
    var regex = /^(20|21|22|23|[0-1][0-9]):[0-5][0-9]$/;
    if (regex.test(strTime)) {
       return (strTime);
    }
        
    return '00:00';
}

$('.hours').on('focusout',function(event) {
    var rep = validateTime($(event.currentTarget).val());
    $(event.currentTarget).val(rep);
});

$(window).resize(function() {
   if($('#calendar').height() < 520) {
        $('.fc-header-title h2').css('font-size', '20px');
   } else {
         $('.fc-header-title h2').css('font-size', '');
   }
});

$('.filterO').click(function () {
    var numberOfChecked = $('.filterO:checkbox:checked').length;
    var totalCheckboxes = $('.filterO:checkbox').length;
    var selected = [];

    $('.filterO:checkbox:checked').each(function () {
        selected.push($(this).attr('name'));
    });
    //if all checkboxes or none checkboxes are checked display all events
    if ((totalCheckboxes - numberOfChecked === 0) || (numberOfChecked === 0)) {
        $('#calendar').fullCalendar('clientEvents', function (eventObject) {
            eventObject.visible = true;
        });
        $('#calendar').fullCalendar('rerenderEvents');
    } else {
        for (var i = 0; i < selected.length; i++) {
            $('#calendar').fullCalendar('clientEvents', function (eventObject) {
                var title = eventObject.owner;

                if (selected[i].indexOf(title) > -1) {
                    eventObject.visible = true;
                    return true;
                } else {
                    eventObject.visible = false;
                    return false;
                }
            });
            $('#calendar').fullCalendar('rerenderEvents');
        }
    }
});

$('body').on('click','.pop-close', function () {
    $(this).parents('.popover').first().remove();
    $('#calendar').fullCalendar('rerenderEvents');
});

$('#agenda_form_allDay').on('click',function() {
    if ($('#agenda_form_allDay').is(':checked')) {
        $('#agenda_form_start').attr('disabled','disabled');
        $('#agenda_form_startHours').attr('disabled','disabled');
        $('#agenda_form_endHours').attr('disabled','disabled');
        $('#agenda_form_end').attr('disabled','disabled');
    } else {
        $('#agenda_form_start').removeAttr('disabled');
        $('#agenda_form_startHours').removeAttr('disabled');
        $('#agenda_form_endHours').removeAttr('disabled');
        $('#agenda_form_end').removeAttr('disabled');
    }
});

