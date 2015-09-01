(function() {
    var $calendar = $('#calendar');
    var isFormShown = false;

    $calendar.fullCalendar({
        header: {
            left: 'prev,next, today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
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
            agendaWeek: trans('agenda.week'),
            agendaDay: trans('agenda.day_')
        },
        monthNames: trans(['agenda.month.january', 'agenda.month.february', 'agenda.month.march', 'agenda.month.april', 'agenda.month.may', 'agenda.month.june', 'agenda.month.july', 'agenda.month.august', 'agenda.month.september', 'agenda.month.october', 'agenda.month.november', 'agenda.month.december']),
        monthNamesShort: trans(['agenda.month.jan', 'agenda.month.feb', 'agenda.month.mar', 'agenda.month.apr', 'agenda.month.may', 'agenda.month.ju', 'agenda.month.jul', 'agenda.month.aug', 'agenda.month.sept',  'agenda.month.oct', 'agenda.month.nov', 'agenda.month.dec']),
        dayNames: trans(['agenda.day.sunday', 'agenda.day.monday', 'agenda.day.tuesday', 'agenda.day.wednesday', 'agenda.day.thursday', 'agenda.day.friday', 'agenda.day.saturday']),
        dayNamesShort: trans(['agenda.day.sun', 'agenda.day.mon', 'agenda.day.tue', 'agenda.day.wed', 'agenda.day.thu', 'agenda.day.fri', 'agenda.day.sat']),
        weekNumberTitle: trans('agenda.week_number_title'),
        dayClick: onDayClick,
        eventClick: onEventClick,
        eventRender: onEventRender,
        eventDrop: onEventDrop,
        eventResize: onEventResize,
        eventDestroy: onEventDestroy,
        eventMouseover: onEventMouseover,
        eventMouseout: onEventMouseout
    });

    function onDayClick(date)
    {
        if (!isFormShown) {
            var routing = Routing.generate('formalibre_add_reservation'),
                momentDate = moment(date);

            var onReservationFormOpen = function(html) {
                $('#reservation_form_start').val(momentDate.format('DD/MM/YYYY HH:mm'));
                $('#reservation_form_end').val(momentDate.add(1, 'hour').format('DD/MM/YYYY HH:mm'));

                initializeDateTimePicker();
            };

            Claroline.Modal.displayForm(
                routing,
                onReservationCreated,
                onReservationFormOpen,
                'form-reservation'
            );

            isFormShown = true;
        }
    }

    function onEventClick(event)
    {
        if (event.editable && !isFormShown) {
            var routing = Routing.generate('formalibre_change_reservation_form', {id: event.reservationId});

            Claroline.Modal.displayForm(
                routing,
                onReservationChanged,
                function() {
                    $('#reservation_form_end').change();
                    initializeDateTimePicker();
                },
                'form-reservation'
            );
            isFormShown = true;
        }
    }

    function onEventRender(event, $element)
    {
        if (!event.visible && event.visible != undefined) {
            return false;
        }
        createPopover(event, $element);
    }

    function onEventDrop(event, delta, revertFunc)
    {
        resizeOrDrop(event, delta, 'move', revertFunc);
    }

    function onEventResize(event, delta, revertFunc)
    {
        resizeOrDrop(event, delta, '', revertFunc);
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
        updateEvent(event);
    }

    function resizeOrDrop(event, delta, action, revertFunc)
    {
        var minutes = moment.duration(delta).asMinutes(),
            routeName = action === 'move' ? 'formalibre_reservation_move' : 'formalibre_resize_reservation',
            routing = Routing.generate(routeName, {id: event.reservationId, minutes: minutes});

        $.ajax({
            url: routing,
            type: 'post',
            dataType: 'json',
            success: function(data) {
                if (data.error == undefined) {
                    updateEvent(data);
                } else {
                    revertFunc();
                    Claroline.Modal.simpleContainer(trans('error_'), trans(data.error));
                }
            }
        });
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
        .on('dp.change', '#reservation_form_start, #reservation_form_end', function() {
            var end = moment($('#reservation_form_end').val(), 'DD/MM/YYYY HH:mm'),
                start = moment($('#reservation_form_start').val(), 'DD/MM/YYYY HH:mm'),
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
                    start = moment($('#reservation_form_start').val(), 'DD/MM/YYYY HH:mm'),
                    newEnd = start.add({hours: hours, minutes: minutes});

                $('#reservation_form_end').val(newEnd.format('DD/MM/YYYY HH:mm'));
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
        // Set isFormShown to false when the modal is closed
        .on('hide.bs.modal', '.modal', function() {
            isFormShown = false;
        })
    ;

    $('.filters-list > a').click(function(e) {
        e.preventDefault();

        if ($(this).is('.active-filter')) {
            $(this).removeClass('active-filter').next().children()
                .each(function() {
                    if ($(this).is('.active-filter')) {
                       $(this).click();
                    }
                });
        } else {
            $(this).addClass('active-filter').next().children()
                .each(function() {
                    if (!$(this).is('.active-filter')) {
                        $(this).click();
                    }
                });
        }

        applyFilters();
    });

    $('.resources-filter > a').click(function(e) {
        e.preventDefault();

        if ($(this).is('.active-filter')) {
            $(this)
                .css({
                    'background-color': '#FFF',
                    'border-color': '#ddd',
                    color: $(this).css('background-color')
                })
                .removeClass('active-filter')
                .parent().prev().removeClass('active-filter')
            ;
        } else {
            $(this)
                .addClass('active-filter')
                .css({
                    'background-color': $(this).css('color'),
                    'border-color': $(this).css('color'),
                    color: '#FFF'
                })
            ;

            if ($(this).parent().children().length === $(this).parent().children('.active-filter').length) {
                $(this).parent().prev().addClass('active-filter');
            }
        }

        applyFilters();
    });

    function initializeDateTimePicker()
    {
        var dateTimePickerOptions = {
            format: 'DD/MM/YYYY HH:mm',
            useCurrent: false,
            locale: trans('picker.locale'),
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-dot-circle-o',
                clear: 'fa fa-trash',
                close: 'fa fa-times'
            },
            stepping: 5,
            showTodayButton: true,
            showClose: true,
            tooltips: {
                today: trans('picker.go_to_today'),
                close: trans('picker.close'),
                selectMonth: trans('picker.select_month'),
                prevMonth: trans('picker.prev_month'),
                nextMonth: trans('picker.next_month'),
                selectYear: trans('picker.select_year'),
                prevYear: trans('picker.prev_year'),
                nextYear: trans('picker.next_year'),
                selectDecade: trans('picker.select_decade'),
                prevDecade: trans('picker.prev_decade'),
                nextDecade: trans('picker.next_decade'),
                prevCentury: trans('picker.prev_century'),
                nextCentury: trans('picker.next_century')
            }
        };

        $('#reservation_form_start').datetimepicker(dateTimePickerOptions);
        $('#reservation_form_end').datetimepicker(dateTimePickerOptions);
    }

    function applyFilters()
    {
        var $resourcesChecked = $('.resources-filter > a.active-filter'),
            resourcesIdChecked = [];

        $.each($resourcesChecked, function() {
            resourcesIdChecked.push($(this).data('resource-id'));
        });

        $calendar.fullCalendar('clientEvents', function(event) {
            if (resourcesIdChecked.length === 0) {
                event.visible = 1;
            } else {
                event.visible = $.inArray(event.resourceId, resourcesIdChecked) === -1 ? 0 : 1;
            }
        });

        $calendar.fullCalendar('rerenderEvents');
    }

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
        event.start.string = event.start.format('DD/MM/YYYY HH:mm');
        event.end.string = event.end.format('DD/MM/YYYY HH:mm');

        $element.popover({
            title: event.title,
            content: Twig.render(ReservationContent, {event: event}),
            html: true,
            container: 'body',
            placement: 'top'
        });
    }

    function updateEvent(event)
    {
        $calendar.fullCalendar('removeEvents', event.id);
        $calendar.fullCalendar('renderEvent', event);
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