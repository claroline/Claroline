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

    calendar.initialize = function (context) {
        context = context || 'desktop';
        var clickedDate = null,
            id = null,
            url = null,
            task = null;

        $('.filter').click(function () {
            var numberOfChecked = $('.filter:checkbox:checked').length;
            var totalCheckboxes = $('.filter:checkbox').length;
            var selected = [];

            $('.filter:checkbox:checked').each(function () {
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
                        var reg = new RegExp(' : ', 'g');
                        var title = eventObject.title.split(reg);

                        if (selected.indexOf(title[0]) < 0) {
                            eventObject.visible = false;
                            return true;
                        } else {
                            eventObject.visible = true;
                            return false;
                        }
                    });
                    $('#calendar').fullCalendar('rerenderEvents');
                }
            }
        });

        var dayClickWorkspace = function (date) {
            $('#myModalLabel').text(Translator.get('agenda' + ':' + 'add_event'));
            clickedDate = date;
            $('#deleteBtn').hide();
            $('#save').show();
            $('#updateBtn').hide();
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').val('');
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').removeAttr('disabled');
            $('#agenda_form').find('input:radio, input:checkbox')
                .removeAttr('checked')
                .removeAttr('selected')
                .removeAttr('disabled');
            $('#myModalLabel').text(Translator.get('agenda' + ':' + 'add_event'));
            $('#agenda_form_start').val($.fullCalendar.formatDate(date,'dd-MM-yyyy'));
            var currentDate = new Date();
            if( clickedDate > currentDate) {
                $('#agenda_form_end').val($.fullCalendar.formatDate(clickedDate,'dd-MM-yyyy'));
            } else {
                $('#agenda_form_end').val($.fullCalendar.formatDate(currentDate,'dd-MM-yyyy'));
            }

            $('.hours').each(function() {
                $(this).val('00:00');
            });
            $('#myModal').modal();
        };
        var dayClickDesktop = function (date) {
            $('#myModalLabel').text(Translator.get('agenda' + ':' + 'add_event'));
            $('#deleteBtn').hide();
            $('#save').show();
            $('#updateBtn').hide();
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').val('');
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').removeAttr('disabled');
            $('#agenda_form').find('input:radio, input:checkbox')
                .removeAttr('checked')
                .removeAttr('selected')
                .removeAttr('disabled');
            $('.hours').each(function() {
                $(this).val('00:00');
            });
            var currentDate = new Date();
            if( clickedDate > currentDate) {
                $('#agenda_form_end').val($.fullCalendar.formatDate(clickedDate,'dd-MM-yyyy'));
            } else {
                $('#agenda_form_end').val($.fullCalendar.formatDate(currentDate,'dd-MM-yyyy'));
            }
            $('#myModal').modal();
        };
        var dayClickFunction = context === 'desktop' ? dayClickDesktop : dayClickWorkspace;
        $('#save').click(function () {
            if ($('#agenda_form_title').val() !== '') {
                $('#save').attr('disabled', 'disabled');
                $('#agenda_form_start').val($('#agenda_form_start').val()+' '+$('#agenda_form_startHours').val());
                $('#agenda_form_end').val($('#agenda_form_end').val()+' '+$('#agenda_form_endHours').val());
                var data = new FormData($('#myForm')[0]);
                data.append('agenda_form[description]',$('#agenda_form_description').val());
                var url = $('#myForm').attr('action');
                $.ajax({
                    'url': url,
                    'type': 'POST',
                    'data': data,
                    'processData': false,
                    'contentType': false,
                    'success': function (data, textStatus, xhr) {
                        if (xhr.status === 200) {
                            $('#myModal').modal('hide');
                            $('#save').removeAttr('disabled');
                            if (data.allDay === false) {
                                $('#calendar').fullCalendar(
                                    'renderEvent',
                                    {
                                        id: data.id,
                                        title: data.title,
                                        start: data.start,
                                        end: data.end,
                                        allDay: data.allDay,
                                        color: data.color,
                                        description : data.description
                                    }
                                );
                                
                                $('#calendar').fullCalendar('unselect');
                            } else {
                                $.ajax({
                                    'url': $('a#taska').attr('href'),
                                    'type': 'GET',
                                    'success': function (data, textStatus, xhr) {
                                        $("#tasks").html(data);
                                        
                                    }
                                });
                            }
                        }
                    },
                    'error': function ( xhr, textStatus) {
                        if (xhr.status === 400) {//bad request
                            alert(Translator.get('agenda' + ':' + 'date_invalid'));
                            $('#save').removeAttr('disabled');
                        } else {
                            //if we got to this point we know that the controller
                            //did not return a json_encoded array. We can assume that
                            //an unexpected PHP error occured
                            alert(Translator.get('agenda' + ':' + 'error'));
                            $('#save').removeAttr('disabled');
                        }
                    }
                });
            } else {
                alert(Translator.get('agenda' + ':' + 'title'));
            }
        });

        $('#updateBtn').click(function () {
           if(task === 'no') {
                var event2 = new Object();
                event2.id = id;
                event2.title = $('#agenda_form_title').val();
                event2.start = $('#agenda_form_start').val()+' '+$('#agenda_form_startHours').val();
                event2.end = $('#agenda_form_end').val()+' '+$('#agenda_form_endHours').val();
                event2.allDay = $('#agenda_form_allDay').attr('checked') === 'checked' ? 1 : 0;
                event2.color = $('#agenda_form_priority').val();
                event2.description = $('#agenda_form_description').val();
                var event1 = $('#calendar').fullCalendar('clientEvents', id);
                var compare = compareEvents(event1[0], event2 );
           } else {
                compare = 1;
           }
            if (compare > 0 ) {
                if ($('#agenda_form_title').val() !== '') {
                    $('#agenda_form_start').val($('#agenda_form_start').val()+' '+$('#agenda_form_startHours').val());
                    $('#agenda_form_end').val($('#agenda_form_end').val()+' '+$('#agenda_form_endHours').val());
                    $('#updateBtn').attr('disabled', 'disabled');
                    var data = new FormData($('#myForm')[0]);
                    data.append('id', id);
                    data.append('agenda_form[description]',$('#agenda_form_description').val());
                    var allDay = $('#agenda_form_allDay').attr('checked') === 'checked' ? 1 : 0;
                    data.append('agenda_form[allDay]', allDay);
                    url = $('a#update').attr('href');
                    $.ajax({
                        'url': url,
                        'type': 'POST',
                        'data': data,
                        'processData': false,
                        'contentType': false,
                        'success': function (data, textStatus, xhr) {
                            $('#myModal').modal('hide');
                            $('#updateBtn').removeAttr('disabled');
                            $('#calendar').fullCalendar('refetchEvents');
                            $.ajax({
                                'url': $('a#taska').attr('href'),
                                'type': 'GET',
                                'success': function (data, textStatus, xhr) {
                                    $("#tasks").html(data);
                                }
                            });
                        },
                        'error': function ( xhr, textStatus) {
                            if (xhr.status === 400) {//bad request
                                alert(Translator.get('agenda' + ':' + 'error'));
                                $('#save').removeAttr('disabled');
                                $('#output').html(textStatus);
                            }
                        }
                    });
                } else {
                    alert(t('title'));
                }
            } else {
                $('#myModal').modal('hide');
            }
        });

        var deleteClick = function (id) {
            $('#deleteBtn').attr('disabled', 'disabled');
            url = $('a#delete').attr('href');
            $.ajax({
                'type': 'POST',
                'url': url,
                'data': {
                    'id': id
                },
                'success': function (data, textStatus, xhr) {
                    if (xhr.status === 200) {
                        $('#myModal').modal('hide');
                        $('#deleteBtn').removeAttr('disabled');
                        $('#calendar').fullCalendar('removeEvents', id);
                    }
                }
            });
        };

        /*
        * function to delete a task
        currentTarget = the object clicked
        */
        $('.delete-task').on('click', function (e) {
            var id = $(e.currentTarget).attr('data-event-id');
            deleteClick(id);
        });
        $('#tasks').on('click','.update-task',function(e) {
            $('#deleteBtn').show();
            $('#save').hide();
            $('#updateBtn').show();
            task = 'task';
            var list = e.target.parentElement.children;
            $('#myModal').modal('show');
            id = $(list[3])[0].innerHTML;
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').val('');
            $('#myModalLabel').text(Translator.get('agenda' + ':' + 'modify'));
            $('#agenda_form_title')
                .attr('value', $(e.target.parentElement.parentElement.children)[1].innerHTML);
            var description = $(list[0])[0].innerHTML == t('no_description') ? '' : $(list[0])[0].innerHTML;
            $('#agenda_form_description').val(description);
            if ($(list[1])[0].innerHTML === 1) {
                $('#agenda_form_allDay').attr('checked', true);
                $('#agenda_form_start').attr('disabled','disabled');
                $('#agenda_form_startHours').attr('disabled','disabled');
                $('#agenda_form_endHours').attr('disabled','disabled');
                $('#agenda_form_end').attr('disabled','disabled');
            }
            $('#agenda_form_priority option[value=' + $(list[2])[0].innerHTML + ']').attr('selected', 'selected');
        });
        function dropEvent(event, dayDelta, minuteDelta) {
            $.ajax({
                'url': $('a#move').attr('href'),
                'type': 'POST',
                'data': {
                    'id': event.id,
                    'dayDelta': dayDelta,
                    'minuteDelta': minuteDelta
                },
                'success': function (data, textStatus, xhr) {
                    //the response is in the data variable

                    if (xhr.status  === 200) {
                        alert(Translator.get('agenda' + ':' + 'event_update'));
                    } else if (xhr.status === 500) {//internal server error
                        alert(Translator.get('agenda' + ':' + 'error'));
                        $('#output').html(data);
                    }
                    else {
                        //if we got to this point we know that the controller
                        //did not return a json_encoded array. We can assume that
                        //an unexpected PHP error occured
                        alert(Translator.get('agenda' + ':' + 'error'));

                        //if you want to print the error:
                        $('#output').html(data);
                    }
                }
            });
        }
        function modifiedEvent(calEvent, context) {
            id = calEvent.id;
            task = 'no';
            if (calEvent.editable === false) {
                $('#deleteBtn').hide();
                $('#updateBtn').hide();
                $('#save').hide();
            } else {
                $('#deleteBtn').show();
                $('#updateBtn').show();
                $('#save').hide();
            }
            $('#myModalLabel').text(Translator.get('agenda' + ':' + 'modify'));
            var title = calEvent.title;
            if (context === 'desktop') {
                var reg = new RegExp('[:]+', 'g');
                title = title.split(reg);
                $('#agenda_form_title').attr('value', title[1]);
            } else {
                $('#agenda_form_title').attr('value', title);
            }
            $('#agenda_form_description').val(calEvent.description);
            $('#agenda_form_priority option[value=' + calEvent.color + ']').attr('selected', 'selected');
            var pickedDate = new Date(calEvent.start);
            $('#agenda_form_start').val($.fullCalendar.formatDate( pickedDate,'dd-MM-yyyy'));
            $('#agenda_form_startHours').val($.fullCalendar.formatDate( pickedDate,'HH:mm'));
            if (calEvent.end === null) {
                $('#agenda_form_end').val($.fullCalendar.formatDate( pickedDate,'dd-MM-yyyy'));
                $('#agenda_form_endHours').val('00:00');
            } else {
                var Enddate = new Date(calEvent.end);
                $('#agenda_form_end').val($.fullCalendar.formatDate( Enddate,'dd-MM-yyyy'));
                $('#agenda_form_endHours').val($.fullCalendar.formatDate( Enddate,'HH:mm'));
            }
            $('#agenda_form_allDay').attr('checked', false);
            $.ajaxSetup({
                'type': 'POST',
                'error': function (xhr, textStatus) {
                    if (xhr.status === 500) {//bad request
                        alert(Translator.get('agenda' + ':' + 'error'));
                    }
                }
            });

            $('#myModal').modal();
        }
        $('#deleteBtn').on('click', function () {
            deleteClick(id);
        });
        $('body').on('click','.launch', function(e) {
                var event1 = $('#calendar').fullCalendar('clientEvents', $(e.currentTarget).attr('data-id'));
                modifiedEvent(event1[0], context);
            }
        );
        function compareEvents(event1 , event2)
        {
            event1.start = $.fullCalendar.formatDate(new Date(event1.start),'dd-MM-yyyy HH:mm');
            // if the start & end date are the same end date is null for the fullcalendar
            event1.end = (event1.end != null) ? $.fullCalendar.formatDate(new Date(event1.end),'dd-MM-yyyy HH:mm'): null;
            event1.allDay = (event1.allDay == false ) ? 0 : 1;
            if (event1.title === event2.title) {
                if (event1.start === event2.start) {
                    if((event1.end === event2.end) || (!event1.end)) {
                        if ((!event1.description) && (!event2.description ) || (event1.description === event2.description)) {
                            if(event1.allDay === event2.allDay) {
                                if(event1.color === event2.color) {
                                  return 0 ;
                                } else 
                                    return 1;
                            } else 
                                return 2;
                        } else
                            return 3;
                    } else
                        return 4;
                }     
                else
                    return 5;
            }
            return 6;
        }

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
            dayClick: dayClickFunction,
            eventClick:  function (event) {
                id = event.id;
            },
            eventRender: function (event, element) {
                if (event.visible === false)
                {
                    return false;
                }

                var eventContent = '';
                eventContent += '<a href="#" data-target="#myModal" role="button" data-toggle="modal" class="launch" data-id='+event.id+'>';
                eventContent +=     Translator.get('platform' + ':' + 'edit');
                eventContent += '</a>';
                eventContent += '<div>';
                eventContent +=     t('agenda_form_start') + ' : ' + $.fullCalendar.formatDate(event.start ,'dd-MM-yyyy HH:mm');
                eventContent += '</div>';
                eventContent += '<div class="mypopo' + event.id + '">';
                eventContent +=     t('agenda_form_end') +':'  + $.fullCalendar.formatDate(event.end ,'dd-MM-yyyy HH:mm');
                eventContent += '</div>';

                if (typeof event.description !== 'undefined' && event.description !== null && event.description.length !== 0) {
                    eventContent += '<div style="word-break:break-all;">';
                    eventContent +=     'Description: ' + event.description;
                    eventContent += '</div>';
                }

                element.popover({
                    title: event.title + '<button type="button" class="pop-close close" data-dismiss="popover" aria-hidden="true">&times;</button>',
                    content: eventContent,
                    html: true,
                    container: 'body'
                });
            },
            eventResize: function (event, dayDelta, minuteDelta) {
                $.ajax({
                    'url': $('a#move').attr('href'),
                    'type': 'POST',
                    'data' : {
                        'id': event.id,
                        'dayDelta': dayDelta,
                        'minuteDelta': minuteDelta
                    },
                    'success': function (data, textStatus, xhr) {
                        //the response is in the data variable

                        if (xhr.status === 200) {
                            alert(Translator.get('agenda' + ':' + 'event_update'));
                        }
                        else {
                            //if we got to this point we know that the controller
                            //did not return a json_encoded array. We can assume that
                            //an unexpected PHP error occured
                            alert(Translator.get('agenda' + ':' + 'error'));

                            //if you want to print the error:
                            $('#output').html(data);
                        }
                    }
                });
            }
        });
    };
}) ();
