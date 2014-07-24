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
        return Translator.get('agenda' + ':' + key);
    }

    calendar.initialize = function (
        context,
        workspaceId
    ) {
        context = context || 'desktop';
        workspaceId = workspaceId || null;

        //initialize route & url depending on the context
        if (context !== 'desktop') {
            calendar.addUrl = Routing.generate('claro_workspace_agenda_add_event_form', {'workspace': workspaceId});
            calendar.showUrl = Routing.generate('claro_workspace_agenda_show', {'workspace': workspaceId});
        } else {
            var addRoute = 'some route i still habe to do';
            var showUrl = 'some other route';
        }

        $('#import-ics-btn').on('click', function (event) {
            event.preventDefault();
            window.Claroline.Modal.displayForm(
                $(event.target).attr('href'),
                addItemsToCalendar,
                function(){},
                'ics-import-form'
            );
        });

        $('body').on('click', '.delete-event', function (event) {
            event.preventDefault();

            window.Claroline.Modal.confirmRequest(
                $(event.currentTarget).attr('href'),
                removeEvent,
                undefined,
                Translator.get('platform:remove_event'),
                Translator.get('platform:remove_event_confirm')
            );
        });

        //popover edit button: trigger the edit form
        $('body').on('click', '.edit-event-link', function(event) {
            event.preventDefault();
            window.Claroline.Modal.displayForm(
                $(event.currentTarget).attr('href'),
                updateCalendarItemCallback,
                function() {},
                'form-event'
            );
        });

        //INITIALIZE CALENDAR
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            columnFormat: {
                month: 'ddd',
                week: 'ddd d/M',
                day: 'dddd d/M'
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
            firstDay:1,
            monthNames: [t('january'), t('february'), t('march'), t('april'), t('may'), t('june'), t('july'),
                t('august'), t('september'), t('october'), t('november'), t('december')],
            monthNamesShort: [t('jan'), t('feb'), t('mar'), t('apr'), t('may'), t('ju'), t('jul'),
                t('aug'), t('sept'), t('nov'), t('dec')],
            dayNames: [ t('sunday'),t('monday'), t('tuesday'), t('wednesday'), t('thursday'), t('friday'), t('saturday')],
            dayNamesShort: [ t('sun'), t('mon'), t('tue'), t('wed'), t('thu'), t('fri'), t('sat')],
            editable: true,
            //This is the url wich will get the events from ajax the 1st time the calendar is launched
            events: calendar.showUrl,
            axisFormat: 'HH:mm',
            timeFormat: 'H(:mm)',
            agenda: 'h:mm{ - h:mm}',
            '': 'h:mm{ - h:mm}',
            minTime: 0,
            maxTime: 24,
            allDaySlot: false,
            lazyFetching : false,
            eventDrop: function (event, dayDelta, minuteDelta) {
                dropEvent(event, dayDelta, minuteDelta);
            },
            dayClick: renderAddEventForm,
            eventClick:  function (event) {
                //don't do anything because it's the "edit" button from the popover that is going to trigger the modal
            },
            //renders the popover for an event
            eventRender: function (event, element) {
                if (event.visible == false) return false;

                event['startFormatted'] = $.fullCalendar.formatDate(
                    event.start,
                    Translator.get('platform:date_agenda_display_format')
                );
                event['endFormatted'] = $.fullCalendar.formatDate(
                    event.end,
                    Translator.get('platform:date_agenda_display_format')
                );

                var eventContent = Twig.render(EventContent, {'event': event});

                element.popover({
                    title: event.title + '<button type="button" class="pop-close close" data-dismiss="popover" aria-hidden="true">&times;</button>',
                    content: eventContent,
                    html: true,
                    container: 'body'
                });
            },
            eventResize: function (event, dayDelta, minuteDelta) {

            }
        });
    };

    var hidePopovers = function() {
        $('.fc-event').popover('hide');
    }

    var renderAddEventForm = function (date) {
        var dateVal = $.fullCalendar.formatDate(
            date,
            Translator.get('platform:date_agenda_display_format')
        );

        var postRenderAddEventAction = function(html) {
            $('#agenda_form_start').val(dateVal);
            $('#agenda_form_end').val(dateVal);

            //add js to hide date if task.
        }

        window.Claroline.Modal.displayForm(
            calendar.addUrl,
            addItemsToCalendar,
            postRenderAddEventAction,
            'form-event'
        );
    };

    var addEventToCalendar = function (event) {
        $('#calendar').fullCalendar(
            'renderEvent',
            {
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                allDay: event.allDay,
                color: event.color,
                description : event.description
            }
        );
    };

    var addTaskToCalendar = function (event) {
        var html = Twig.render(Task, {'event': event});
        $('#tasks-list').append(html);
    }

    var addItemsToCalendar = function (events) {
        for (var i = 0; i < events.length; i++) {
            events[i].allDay ? addTaskToCalendar(events[i]):  addEventToCalendar(events[i]);
        }
    }

    var updateCalendarItem = function (event) {
        console.debug(event);
        removeEvent(undefined, undefined, event);
        addItemsToCalendar(new Array(event));
    }

    var updateCalendarItemCallback = function (event) {
        hidePopovers();
        updateCalendarItem(event);
    }

    var removeEvent = function(event, item, data) {
        hidePopovers();
        //Remove from the calendar if it exists.
        $('#calendar').fullCalendar('removeEvents', data.id);
        //Remove from the task bar if it exists.
        $('#li-task-' + data.id).hide();
    }
}) ();
