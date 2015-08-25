(function() {
    var $calendar = $('#calendar');

    $calendar.fullCalendar({
        header: {
            left: 'prev,next, today',
            center: 'title',
            right: 'month,agendaWeek'
        },
        columnFormat: {
            month: 'ddd',
            week: 'ddd D/M'
        },
        firstDay: 1,
        timezone: 'local',
        fixedWeekCount: false,
        weekNumbers: true,
        allDaySlot: false,
        axisFormat: 'HH:mm',
        timeFormat: 'H:mm',
        agenda: 'h:mm{ - h:mm}',
        eventLimit: true,
        events: Routing.generate('formalibre_reservation_agenda_show'),
        buttonText: {
            today: trans('agenda.today'),
            month: trans('agenda.month_'),
            agendaWeek: trans('agenda.week')
        },
        monthNames: trans(['agenda.month.january', 'agenda.month.february', 'agenda.month.march', 'agenda.month.april', 'agenda.month.may', 'agenda.month.june', 'agenda.month.july', 'agenda.month.august', 'agenda.month.september', 'agenda.month.october', 'agenda.month.november', 'agenda.month.december']),
        monthNamesShort: trans(['agenda.month.jan', 'agenda.month.feb', 'agenda.month.mar', 'agenda.month.apr', 'agenda.month.may', 'agenda.month.ju', 'agenda.month.jul', 'agenda.month.aug', 'agenda.month.sept',  'agenda.month.oct', 'agenda.month.nov', 'agenda.month.dec']),
        dayNames: trans(['agenda.day.sunday', 'agenda.day.monday', 'agenda.day.tuesday', 'agenda.day.wednesday', 'agenda.day.thursday', 'agenda.day.friday', 'agenda.day.saturday']),
        dayNamesShort: trans(['agenda.day.sun', 'agenda.day.mon', 'agenda.day.tue', 'agenda.day.wed', 'agenda.day.thu', 'agenda.day.fri', 'agenda.day.sat']),
        weekNumberTitle: trans('agenda.week_number_title'),
        dayClick: onDayClick,
        eventClick: onEventClick,
        eventRender: onEventRender,
        eventDestroy: onEventDestroy,
        eventMouseover: onEventMouseover,
        eventMouseout: onEventMouseout
    });

    function onDayClick(date)
    {
        var routing = Routing.generate('formalibre_add_reservation'),
            dateDate = moment(date).format('YYYY-MM-DD'),
            dateTime = moment(date).format('HH:mm');

        var onReservationFormOpen = function(html) {
            $('#reservation_form_start_date').val(dateDate);
            $('#reservation_form_start_time').val(dateTime);
        };

        Claroline.Modal.displayForm(routing, onReservationCreated, onReservationFormOpen, 'form-reservation');
    }

    function onEventClick(event)
    {
        var routing = Routing.generate('formalibre_change_reservation_form', {id: event.reservationId});

        Claroline.Modal.displayForm(routing, onReservationChanged, function(){
            $('#reservation_form_end_time').change();
        }, 'form-reservation');
    }

    function onEventRender(event, $element)
    {
        createPopover(event, $element);
    }

    function onEventDestroy(event, $element)
    {
        $element.popover('destroy');
    }

    function onEventMouseover()
    {
        $(this).popover('show');
    }

    function onEventMouseout()
    {
        $(this).popover('hide');
    }

    function onReservationChanged(event)
    {
        $calendar.fullCalendar('removeEvents', event.id);
        $calendar.fullCalendar('renderEvent', event);
    }

    $('body')
        // Show details of the selected resource
        .on('change', 'select#reservation_form_resource',  function() {
            var $this = $(this),
                resourceId = $this.val(),
                routing = Routing.generate('formalibre_reservation_get_resource_info', {id: resourceId});

            $.ajax({
                url: routing,
                type: 'get',
                dataType: 'json',
                success: function(data) {
                    $('#reservation_form_resource_description').text(data.description);
                    $('#reservation_form_resource_localisation').text(data.localisation);
                    $('#reservation_form_resource_max_time').text(data.maxTime);
                }
            });
        })
        // Change the duration input when the end input is changed
        .on('change', '#reservation_form_start_date, #reservation_form_start_time, #reservation_form_end_date, #reservation_form_end_time', function() {
            var end = moment($('#reservation_form_end_date').val() +' '+ $('#reservation_form_end_time').val()),
                start = moment($('#reservation_form_start_date').val() +' '+ $('#reservation_form_start_time').val()),
                duration = end.diff(start, 'minutes'),
                hours = Math.floor(duration / 60),
                minutes = duration - hours * 60  < 10 ? '0'+ (duration - hours * 60) : duration - hours * 60;

            $('#reservation_form_duration').val(hours +':'+ minutes);
        })
        // Change the end input when the duration input is changed
        .on('keyup', '#reservation_form_duration', function() {
            var duration = $(this).val(),
                durationArray = duration.split(':');

            if (durationArray.length === 2) {
                var hours = isNaN(durationArray[0]) ? 0 : durationArray[0],
                    minutes = isNaN(durationArray[1]) ? 0 : durationArray[1],
                    start = moment($('#reservation_form_start_date').val() +' '+ $('#reservation_form_start_time').val()),
                    newEnd = start.add({hours: hours, minutes: minutes});

                $('#reservation_form_end_date').val(newEnd.format('YYYY-MM-DD'));
                $('#reservation_form_end_time').val(newEnd.format('HH:mm'));
            }
        })
        // Delete a reservation
        .on('click', '.delete-reservation', function() {
            var reservationId = $(this).data('reservation-id'),
                eventId = $(this).data('event-id'),
                routing = Routing.generate('formalibre_delete_reservation', {id: reservationId});

            Claroline.Modal.confirmRequest(
                routing,
                onReservationDeleted, eventId,
                trans('confirm_reservation_deletion_content'),
                trans('confirm_reservation_deletion_title')
            );
        })
    ;

    function onReservationDeleted(event, eventId)
    {
        $calendar.fullCalendar('removeEvents', eventId);
    }

    function onReservationCreated(event)
    {
        $calendar.fullCalendar('renderEvent', event);
    }

    function createPopover(event, $element)
    {
        event.start.string = moment(event.start._i).format('DD/MM/YYYY HH:mm');
        event.end.string = moment(event.end._i).format('DD/MM/YYYY HH:mm');

        $element.popover({
            title: event.title,
            content: Twig.render(ReservationContent, {event: event}),
            html: true,
            container: 'body',
            placement: 'top'
        });
    }

    function trans(key)
    {
        if (typeof key === 'object') {
            var transWords = [];
            for (var i = 0; i < key.length; i++) {
                transWords.push(Translator.trans(key[i], {}, 'reservation'));
            }
            return transWords;
        }
        return Translator.trans(key, {}, 'reservation');
    }
}) ();