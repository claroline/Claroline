(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    var calendar = window.Claroline.Calendar = {};

    calendar.initialize = function (context) {
        context = context || 'desktop';
        var clickedDate = null,
            id = null,
            url = null;

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
                        var reg = new RegExp('[:]+', 'g');
                        var title = eventObject.title.split(reg);
                        if (selected.indexOf(title[0]) < 0) {
                            eventObject.visible = false;
                            return true;
                        } else {
                            eventObject.visible = true;
                            return false;
                        }
                    });
                    //console.debug($('#calendar').fullCalendar('clientEvents'));
                    $('#calendar').fullCalendar('rerenderEvents');
                }
            }
        });

        var dayClickWorkspace = function (date) {
            clickedDate = date;
            $('#deleteBtn').hide();
            $('#save').show();
            $('#updateBtn').hide();
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').val('');
            $('#agenda_form').find('input:radio, input:checkbox')
                .removeAttr('checked')
                .removeAttr('selected');
            var  currentDate = new Date();
            var pickedDate = new Date(date);
            $('#agenda_form_start').val(date.toLocaleString())
            if (pickedDate > currentDate) {
                $('#agenda_form_end').val(pickedDate.toLocaleString());
                    
            } else {
                $('#agenda_form_end').val(currentDate.toLocaleString());
            }
            $('#myModal').modal();
        };
        var dayClickDesktop = function () {
            alert('Not implemented yet');
        };
        var dayClickFunction = context === 'desktop' ? dayClickDesktop : dayClickWorkspace;

        $('#save').click(function () {
            if ($('#agenda_form_title').val() !== '') {
                $('#save').attr('disabled', 'disabled');
                var data = new FormData($('#myForm')[0]);
                console.debug(data);
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
                                        title: data.title,
                                        start: data.start,
                                        end: data.end,
                                        allDay: data.allDay,
                                        color: data.color
                                    },
                                    true // make the event 'stick'
                                );
                                $('#calendar').fullCalendar('unselect');
                            }
                        }
                    },
                    'error': function ( xhr, textStatus) {
                        if (xhr.status === 400) {//bad request
                            alert(textStatus);
                            $('#save').removeAttr('disabled');
                            $('#output').html(textStatus);
                        } else {
                            //if we got to this point we know that the controller
                            //did not return a json_encoded array. We can assume that
                            //an unexpected PHP error occured
                            alert('An unexpeded error occured.');
                            $('#save').removeAttr('disabled');
                            //if you want to print the error:
                            $('#output').html(data);
                        }
                    }
                });
            } else {
                alert('title can not be empty');
            }
        });

        $('#updateBtn').click(function () {
            $('#updateBtn').attr('disabled', 'disabled');
            var data = new FormData($('#myForm')[0]);
            data.append('id', id);
            url = $('a#update').attr('href');
            $.ajax({
                'url': url,
                'type': 'POST',
                'data': data,
                'processData': false,
                'contentType': false,
                'success': function (data, textStatus, xhr) {
                    if (xhr.status === 200)  {
                        $('#myModal').modal('hide');
                        $('#updateBtn').removeAttr('disabled');
                        $('#calendar').fullCalendar('refetchEvents');
                    }
                },
                'error': function ( xhr, textStatus) {
                    if (xhr.status === 400) {//bad request
                        alert(textStatus);
                        $('#save').removeAttr('disabled');
                        $('#output').html(textStatus);
                    }
                }
            });
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

        $('.update-task').on('click', function (e) {
            $('#save').hide();
            var list = e.target.parentElement.children;
            $('#myModal').modal('show');
            $('#agenda_form').find('input:text, input:password, input:file, select, textarea').val('');
            $('#myModalLabel').val('Modifier une entrée');
            $('#agenda_form_title')
                .attr('value', $(e.target.parentElement.parentElement.children)[1].innerHTML);
            $('#agenda_form_start').val($(list[0])[0].innerHTML);
            $('#agenda_form_end').val($(list[1])[0].innerHTML);
            $('#agenda_form_description').val($(list[2])[0].innerHTML);
        });
        function dropEvent(event, dayDelta, minuteDelta) {
            id = event.id;
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
                        alert('event update');
                    } else if (xhr.status === 500) {//internal server error
                        alert('An error occured' + data.greeting);
                        $('#output').html(data);
                    }
                    else {
                        //if we got to this point we know that the controller
                        //did not return a json_encoded array. We can assume that
                        //an unexpected PHP error occured
                        alert('An unexpeded error occured.');

                        //if you want to print the error:
                        $('#output').html(data);
                    }
                }
            });

            if (!confirm('Are you sure about this change?')) {
                //@todo revertFunc ? what is this ?
                //revertFunc(); ? what is this ?
                alert('Please tell me what I\'m supposed to do');
            }
        }
        function modifiedEvent(calEvent)
        {
            id = calEvent.id;
            $('#deleteBtn').show();
            $('#updateBtn').show();
            $('#save').hide();
            $('#myModalLabel').text('Modifier une entrée');
            $('#agenda_form_title').attr('value', calEvent.title);
            $('#agenda_form_description').val(calEvent.description);
            $('#agenda_form_priority option[value=' + calEvent.color + ']').attr('selected', 'selected');
            var pickedDate = new Date(calEvent.start);
            $('#agenda_form_start').val(pickedDate.toLocaleString());
            if (calEvent.end === null)
            {

                $('#agenda_form_end').val(pickedDate.toLocaleString());
            }
            else
            {
                var Enddate = new Date(calEvent.end);
                $('#agenda_form_end').val(Enddate.toLocaleString());
            }

            $.ajaxSetup({
                'type': 'POST',
                'error': function (xhr, textStatus) {
                    if (xhr.status === 500) {//bad request
                        alert('An error occured' + textStatus);
                    }
                }
            });

            $('#myModal').modal();
        }
        $('#deleteBtn').on('click', function () {
            deleteClick(id);
        });

        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet',
                'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août',
                'sept.', 'oct.', 'nov.', 'déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            editable: true,
            events: $('a#link').attr('href'),
            timeFormat: 'H(:mm)',
            agenda: 'h:mm{ - h:mm}', // 5:00 - 6:30
            // for all other views
            '': 'h(:mm)t',            // 7p
            allDayText: 'all-day',
            allDaySlot: true,
            eventDrop: function (event, dayDelta, minuteDelta) {
                dropEvent(event, dayDelta, minuteDelta);
            },
            dayClick: dayClickFunction,
            eventClick:  function (calEvent) {
                modifiedEvent(calEvent);
            },
            eventRender: function (event) {
                if (event.visible === false)
                {
                    return false;
                }
            },
            eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
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
                            alert('event update');
                        }
                        else {
                            //if we got to this point we know that the controller
                            //did not return a json_encoded array. We can assume that
                            //an unexpected PHP error occured
                            alert('An unexpeded error occured.');

                            //if you want to print the error:
                            $('#output').html(data);
                        }
                    }
                });
                if (!confirm('is this okay?')) {
                    revertFunc();
                }
            }
        });
    };
}) ();
