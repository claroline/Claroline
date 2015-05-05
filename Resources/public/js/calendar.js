/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    var calendar = window.Claroline.Calendar = {};

    function t(key) {
        return Translator.trans(key, {}, 'agenda');
    }

    calendar.initialize = function (
        context,
        workspaceId,
        canCreate
    ) {
        context = context || 'desktop';
        workspaceId = workspaceId || null;
        //the creation is enabled by default
        if (canCreate === undefined) {
            calendar.canCreate = true;
        } else {
            calendar.canCreate = canCreate;
        }

        //initialize route & url depending on the context
        if (context !== 'desktop') {
            calendar.addUrl = Routing.generate('claro_workspace_agenda_add_event_form', {'workspace': workspaceId});
            calendar.showUrl = Routing.generate('claro_workspace_agenda_show', {'workspace': workspaceId});
        } else {
            calendar.addUrl = Routing.generate('claro_desktop_agenda_add_event_form');
            calendar.showUrl = Routing.generate('claro_desktop_agenda_show');
        }

        $('#import-ics-btn').on('click', function (event) {
            event.preventDefault();
            window.Claroline.Modal.displayForm(
                $(event.target).attr('href'),
                addItemsToCalendar,
                function () {},
                'ics-import-form'
            );
        });

        $('body')
            // Delete the event from the form and the popover button
            .on('click', '.delete-event', function (event) {
                event.preventDefault();
                window.Claroline.Modal.confirmRequest(
                    $(event.currentTarget).attr('href'),
                    removeEvent,
                    undefined,
                    Translator.trans('remove_event_confirm', {}, 'platform'),
                    Translator.trans('remove_event', {}, 'platform')
                );
            })
            // Hide the hours if the checkbox allDay is checked
            .on('click', '#agenda_form_isAllDay', function() {
                $('#agenda_form_isAllDay').is(':checked') ? hideFormhours(): showFormhours();
            })
            // Hide the start date if the task is checked.
            .on('click', '#agenda_form_isTask', function() {
                $('#agenda_form_isTask').is(':checked') ? hideStartDate() : showStartDate();
            })
        ;

        $('.filter,.filter-tasks').click(function () {
            var workspaceIds = [];

            $('.filter:checkbox:checked').each(function () {
                workspaceIds.push(parseInt($(this).val()));
            });

            filterEvents(workspaceIds);
        });

        // INITIALIZE CALENDAR
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next, today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            columnFormat: {
                month: 'ddd',
                week: 'ddd D/M',
                day: 'dddd D/M'
            },
            buttonText: {
                prev: t('prev'),
                next: t('next'),
                prevYear: t('prevYear'),
                nextYear: t('nextYear'),
                today: t('today'),
                month: t('month'),
                week: t('week'),
                day: t('day')
            },
            firstDay: 1,
            monthNames: [t('january'), t('february'), t('march'), t('april'), t('may'), t('june'), t('july'),
                t('august'), t('september'), t('october'), t('november'), t('december')],
            monthNamesShort: [t('jan'), t('feb'), t('mar'), t('apr'), t('may'), t('ju'), t('jul'),
                t('aug'), t('sept'),  t('oct'), t('nov'), t('dec')],
            dayNames: [ t('sunday'),t('monday'), t('tuesday'), t('wednesday'), t('thursday'), t('friday'), t('saturday')],
            dayNamesShort: [ t('sun'), t('mon'), t('tue'), t('wed'), t('thu'), t('fri'), t('sat')],
            editable: true,
            //This is the url wich will get the events from ajax the 1st time the calendar is launched
            events: calendar.showUrl,
            axisFormat: 'HH:mm',
            timeFormat: 'H:mm',
            agenda: 'h:mm{ - h:mm}',
            '': 'h:mm{ - h:mm}',
            allDayText: t('isAllDay'),
            lazyFetching : false,
            fixedWeekCount: false,
            eventDrop: onEventDrop,
            dayClick: renderAddEventForm,
            eventClick:  onEventClick,
            eventDestroy: onEventDestroy,
            eventMouseover: onEventMouseover,
            eventMouseout: onEventMouseout,
            eventRender: onEventRender,
            eventResize: onEventResize
        });

        // If a year is define in the Url, redirect the calendar to that year, month and day
        redirectCalendar();
    };

    var onEventDrop = function (event, delta, revertFunc, jsEvent, ui, view) {
        resizeOrMove(event, delta._days, delta._milliseconds / (1000 * 60), 'move');
    };

    var onEventClick = function (event, jsEvent) {
        var $this = $(this);
        // If click on the check symbol of a task, mark this task as "to do"
        if ($(jsEvent.target).hasClass('fa-check')) {
            markTaskAsToDo(event, jsEvent, $this);
        }
        // If click on the checkbox of a task, mark this task as done
        else if ($(jsEvent.target).hasClass('fa-square-o')) {
            markTaskAsDone(event, jsEvent, $this);
        }
        // Show the modal form if the user can edit the event
        else if (event.editable) {
            showEditForm(event);
        }
    };

    var onEventDestroy = function (event, $element) {
        $element.popover('destroy');
    };

    var onEventMouseover = function () {
        $(this).popover('show');
    };

    var onEventMouseout = function () {
        $(this).popover('hide');
    };

    var onEventRender = function (event, element) {
        // Events are unfiltered by default
        event.visible = event.visible === undefined ? true: event.visible;
        if (!event.visible) return false;
        renderEvent(event, element);
    };

    var onEventResize = function (event, delta, revertFunc, jsEvent, ui, view) {
        resizeOrMove(event, delta._days, delta._milliseconds / (1000 * 60), 'resize');
    };

    var renderEvent = function (event, $element) {
        // Create the popover for the event or the task
        createPopover(event, $element);

        // If it's a task
        if (event.isTask) {
            var eventContent =  $element.find('.fc-content');
            // Remove the date
            eventContent.find('.fc-time').remove();
            $element.css({
                'background-color': 'rgb(144, 32, 32)',
                'border-color': 'rgb(144, 32, 32)'
            });
            eventContent.prepend('<span class="task fa" data-event-id="' + event.id + '"></span>');

            // Add the checkbox if the task is not done or the check symbol if the task is done
            var checkbox = eventContent.find('.task');
            if (event.isTaskDone) {
                checkbox.addClass('fa-check');
                checkbox.next().css('text-decoration', 'line-through');
            } else {
                checkbox.addClass('fa-square-o');
            }
        }
    };

    var renderAddEventForm = function (date) {
        if (calendar.canCreate) {
            var dateVal = moment(date).format(Translator.trans('date_agenda_display_format', {}, 'platform'));

            var postRenderAddEventAction = function (html) {
                $('#agenda_form_start').val(dateVal);
                $('#agenda_form_end').val(dateVal);
            };

            window.Claroline.Modal.displayForm(
                calendar.addUrl,
                addItemsToCalendar,
                postRenderAddEventAction,
                'form-event'
            );
        }
    };

    var addEventAndTaskToCalendar = function (event) {
        if (event.isTask) {
            var html = Twig.render(Task, {'event': event});
            var tasksList = $('#tasks-list');
            if (tasksList.length === 1 && tasksList.find('.no-task')) {
                tasksList.children().first().remove();
            }
            tasksList.append(html);
        }

        $('#calendar').fullCalendar(
            'renderEvent',
            {
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                allDay: event.allDay,
                color: event.color,
                description : event.description,
                deletable: event.deletable,
                editable: event.editable,
                endFormatted: event.endFormatted,
                startFormatted: event.startFormatted,
                owner: event.owner,
                isTask: event.isTask,
                isTaskDone: event.isTaskDone
            }
        );
    };

    var addItemsToCalendar = function (events) {
        for (var i = 0; i < events.length; i++) {
            addEventAndTaskToCalendar(events[i]);
        }
    };

    var updateCalendarItem = function (event) {
        removeEvent(undefined, undefined, event);
        addItemsToCalendar(new Array(event));
    };

    var updateCalendarItemCallback = function (event) {
        updateCalendarItem(event);
    };

    var removeEvent = function (event, item, data) {
        $('#calendar').fullCalendar('removeEvents', data.id);
    };

    var resizeOrMove = function (event, dayDelta, minuteDelta, action) {
        var route = action === 'move' ? 'claro_workspace_agenda_move': 'claro_workspace_agenda_resize';

        $.ajax({
            'url': Routing.generate(route, {'event': event.id, 'day': dayDelta, 'minute': minuteDelta}),
            'type': 'POST',
            'success': function (event) {

            },
            'error': function () {
                //do more error handling here
                alert('error');
                updateCalendarItem(event);
            }
        });
    };

    var filterEvents = function (workspaceIds) {
        var numberOfChecked = $('.filter:checkbox:checked').length;
        var totalCheckboxes = $('.filter:checkbox').length;
        var radioValue = $('input[type=radio].filter-tasks:checked').val();
        // If all checkboxes or none checkboxes are checked display all events
        if (((totalCheckboxes - numberOfChecked === 0) || (numberOfChecked === 0)) && radioValue === 'no-filter-tasks') {
            $('#calendar').fullCalendar('clientEvents', function (eventObject) {
                eventObject.visible = true;
            });
        } else {
            $('#calendar').fullCalendar('clientEvents', function (eventObject) {
                var workspaceId = eventObject.workspace_id === null ? 0 : eventObject.workspace_id;

                if (radioValue === 'no-filter-tasks') {
                    eventObject.visible = $.inArray(workspaceId, workspaceIds) >= 0;
                }
                // Hide all the tasks
                else if (radioValue === 'hide-tasks') {
                    eventObject.visible = !eventObject.isTask;

                    if (!eventObject.isTask) {
                        eventObject.visible = $.inArray(workspaceId, workspaceIds) >= 0 || workspaceIds.length === 0;
                    }
                }
                // Hide all the events
                else {
                    eventObject.visible = eventObject.isTask;

                    if (eventObject.isTask) {
                        eventObject.visible = $.inArray(workspaceId, workspaceIds) >= 0 || workspaceIds.length === 0;
                    }
                }
            });
        }
        $('#calendar').fullCalendar('rerenderEvents');
    };

    var createPopover = function (event, $element) {
        // In FullCalendar 2.3.1, the end date is null when the start date is the same
        if (event.end === null) {
            event.end = event.start;
        }
        convertDateTimeToString();
        $element.popover({
            title: event.title,
            content: Twig.render(EventContent, {'event': event}),
            html: true,
            container: 'body',
            placement: 'top'
        });
    };

    var convertDateTimeToString = function () {
        Twig.setFilter('convertDateTimeToString', function (value, isAllDay) {
            if (isAllDay) {
                // We have to subtract 1 day for the all day event because, it end on the next day at midnight. For example, the end date of a all day event of the 05/04/15 will be on the 06/04/15 00:00. So for a better user experience, we subtract 1 day.
                return moment(value).subtract(1, 'day').format('DD/MM/YYYY')
            } else {
                return moment(value).format('DD/MM/YYYY HH:mm');
            }
        });
    };

    var markTaskAsToDo = function (event, jsEvent, $element) {
        $.ajax({
            url: window.Routing.generate('claro_agenda_set_task_as_not_done', {'event': event.id}),
            type: 'GET',
            success: function() {
                $(jsEvent.target)
                    .removeClass('fa-check')
                    .addClass('fa-square-o')
                    .next().css('text-decoration', 'none');
                $element.popover('destroy');
                event.isTaskDone = false;
                createPopover(event, $element);
            }
        })
    };

    var markTaskAsDone = function (event, jsEvent, $element) {
        $.ajax({
            url: window.Routing.generate('claro_agenda_set_task_as_done', {'event': event.id}),
            type: 'GET',
            success: function() {
                $(jsEvent.target)
                    .removeClass('fa-square-o')
                    .addClass('fa-check')
                    .next().css('text-decoration', 'line-through');
                $element.popover('destroy');
                event.isTaskDone = true;
                createPopover(event, $element);
            }
        })
    };

    var showEditForm = function (event) {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_agenda_update_event_form', {'event': event.id}),
            updateCalendarItemCallback,
            function () {
                $('#agenda_form_isTask').is(':checked') ? hideStartDate() : showStartDate();
                $('#agenda_form_isAllDay').is(':checked') ? hideFormhours(): showFormhours();
            },
            'form-event'
        )
    };

    var hideFormhours = function() {
        $('#agenda_form_endHours').parent().parent().hide();
        $('#agenda_form_startHours').parent().parent().hide();
    };

    var showFormhours = function() {
        $('#agenda_form_endHours').parent().parent().show();
        if (!$('#agenda_form_isTask').is(':checked')) {
            $('#agenda_form_startHours').parent().parent().show();
        }
    };

    var hideStartDate = function() {
        $('#agenda_form_start').parent().parent().hide();
        $('#agenda_form_startHours').parent().parent().hide();
    };

    var showStartDate = function() {
        $('#agenda_form_start').parent().parent().show();
        if (!$('#agenda_form_isAllDay').is(':checked')) {
            $('#agenda_form_startHours').parent().parent().show();
        }
    };

    var getQueryVariable = function (variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");

        for (var i = 0, varsLength = vars.length; i < varsLength; i++) {
            var pair = vars[i].split("=");
            if(decodeURIComponent(pair[0]) == variable){
                return decodeURIComponent(pair[1]);
            }
        }
        return null;
    };

    var redirectCalendar = function () {
        if (getQueryVariable('year')) {
            var year = !isNaN(getQueryVariable('year')) && getQueryVariable('year') ? getQueryVariable('year') : new Date('Y'),
                month = !isNaN(getQueryVariable('month')) && getQueryVariable('month') ? getQueryVariable('month') : new Date('m'),
                day = !isNaN(getQueryVariable('day')) && getQueryVariable('day') ? getQueryVariable('day') : new Date('d');

            $('#calendar').fullCalendar('gotoDate', year + '-' + month + '-' + day);
        }
    };
}) ();