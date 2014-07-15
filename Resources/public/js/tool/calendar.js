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
            var addUrl = Routing.generate('claro_workspace_agenda_add_event_form', {workspace: workspaceId});
            //var showUrl = Routing.generate('claro_workspace_agenda_show', {'workspace': workspaceId});
        } else {
            var addRoute = 'some route i still habe to do';
            var showUrl = 'some other route';
        }

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

        }

        var addItemsToCalendar = function (events) {
            for (var i = 0; i < events.length; i++) {
                events[i].allDay ? addEventToCalendar(events[i]): addTaskToCalendar(events[i]);
            }
        }

        var updateCalendarEvent = function (event) {

        }

        var updateCalendarTask = function (event) {

        }

        var updateCalendarItems = function (events) {
            for (var i = 0; i < events.length; i++) {
                events[i].allDay ? updateCalendarEvent(events[i]): updateCalendarTask(events[i]);
            }
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
            hidePopovers();
            $.ajax({
                url: $(event.currentTarget).attr('href'),
                success: function(event) {
                    hidePopovers();
                    $('#calendar').fullCalendar('removeEvents', event.id);
                }
            });
        });

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
                addUrl,
                addItemsToCalendar,
                postRenderAddEventAction,
                'form-event'
            );
        };

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
            events: $('a#link').attr('href'),
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

    //popover edit button: trigger the edit form
    $('body').on('click', '.edit-event-link', function(event) {
        event.preventDefault();
        window.Claroline.Modal.displayForm(
            $(event.currentTarget).attr('href'),
            hidePopovers,
            function() {},
            'form-event'
        );
    });

    var hidePopovers = function() {
        $('.fc-event').popover('hide');
    }

}) ();
