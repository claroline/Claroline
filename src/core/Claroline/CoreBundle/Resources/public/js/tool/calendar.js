(function () {
    this.Claroline = this.Claroline || {};
    var calendar = this.Claroline.Calendar = {};

    calendar.initialize = function (context) {
        context = context || 'desktop';
        var clickedDate = null;
        var id = null;
        var  url = null;
        var dayClickWorkspace = function (date, allDay, jsEvent, view) {
            clickedDate = date;
            $('#deleteBtn').hide();
            $('#save').show();
            $('#updateBtn').hide();
            $('#calendar_form').find('input:text, input:password, input:file, select, textarea').val('');
            $('#calendar_form').find('input:radio, input:checkbox')
                .removeAttr('checked')
                .removeAttr('selected');

            if (allDay) {  
                var  currentDate = new Date();
                var pickedDate = new Date(date); 
                $('#divTitle').html('Start date '+pickedDate.getDate()+'/'+(pickedDate.getMonth()+1)+'/'+pickedDate.getFullYear());
                
                if (pickedDate > currentDate ) {
                    $('#calendar_form_end_day').val(pickedDate.getDate());
                    $('#calendar_form_end_month').val(pickedDate.getMonth()+1);
                    $('#calendar_form_end_year').val(pickedDate.getFullYear());         
                } else {
                    $('#calendar_form_end_day').val(currentDate.getDate());
                    $('#calendar_form_end_month').val(currentDate.getMonth()+1);
                    $('#calendar_form_end_year').val(currentDate.getFullYear());     
                }

                $( "#myModal" ).modal(); 
            }
        };
        
        var dayClickDesktop = function () {
            alert('Not implemented yet');
        };
        
        var dayClickFunction = context === 'desktop' ? dayClickDesktop : dayClickWorkspace;
        
        $("#save").click(function() {
            if ($('#form_title').val() !== '') {
                $("#save").attr("disabled", "disabled");
                data = new FormData($('#myForm')[0]); 
                data.append("date", new Date(clickedDate));
                var url = $("#myForm").attr("action");
                $.ajax({
                    'url': url,
                    'type': 'POST',
                    'data': data ,
                    'processData': false,
                    'contentType': false,
                    'success': function (data, textStatus, xhr) {
                        if (xhr.status == 200 ) {           
                            $('#myModal').modal('hide') ;
                            $('#save').removeAttr("disabled"); 
                            $('#calendar').fullCalendar('renderEvent',
                            {
                                title: data.title,
                                start: data.start,
                                end: data.end,
                                allDay: data.allDay,
                                color: data.color
                            },
                            true // make the event "stick"
                            );
                            $('#calendar').fullCalendar('unselect');
                        }
                    },
                    'error': function (xhr, textStatus) {
                        if(xhr.status == 400){//bad request
                            alert(" Start date is bigger thand end date");
                            $('#save').removeAttr("disabled"); 
                            $('#output').html(textStatus);
                        } else{
                            //if we got to this point we know that the controller
                            //did not return a json_encoded array. We can assume that           
                            //an unexpected PHP error occured
                            alert("An unexpeded error occured.");
                            $('#save').removeAttr("disabled"); 
                            //if you want to print the error:
                            $('#output').html(data);
                        }
                    }
                });
            } else {
                alert('title can not be empty');
            }
        });
        
        $("#updateBtn").click(function() {
         $("#updateBtn").attr("disabled", "disabled");
         var data = new FormData($('#myForm')[0]);
         data.append('id',id);
         url = $('a#update').attr('href');
         $.ajax({
             'url':url,
             'type':'POST',
             'data':data,
             'processData': false,
             'contentType': false,
             'success': function(data,textStatus,xhr) {
                 if(xhr.status == 200)  { 
                     $('#myModal').modal('hide');
                     $('#updateBtn').removeAttr("disabled"); 
                     $('#calendar').fullCalendar( 'rerenderEvents' );
                   
                 }
             }
         });
     });
     
    $("#deleteBtn").click(function() {
    $("#deleteBtn").attr("disabled", "disabled");
    url = $('a#delete').attr('href');
    $.ajax({
        'url':url,
        'data':{
            'id': id 
        },
        'success': function(data,textStatus, xhr){
            if(xhr.status == 200) {
                $('#deleteBtn').removeAttr("disabled"); 
                $('#calendar').fullCalendar( 'removeEvents',id );
                $('#myModal').modal('hide') ;
                }
            }                 
        });
    });
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,basicWeek,basicDay'
            },
            editable: true,
            events: $("a#link").attr("href"),
            timeFormat: 'H(:mm)',
            agenda: 'h:mm{ - h:mm}',
            allDayText: 'all-day',
            allDaySlot: true,
            eventDrop: function (event,dayDelta,minuteDelta,allDay,revertFunc) {    
                $.ajax({
                    'url':$("a#move").attr("href"),
                    'type':'POST',
                    'data' :{
                        'id':event.id,
                        'dayDelta':dayDelta,
                        'minuteDelta':minuteDelta
                    } ,
                    'success':function(data, textStatus, xhr){
                        //the response is in the data variable

                        if(xhr.status  == 200 ){           
                            alert('event update');             
                        }
                        else if(xhr.status == 500){//internal server error
                            alert("An error occured"+data.greeting);
                            $('#output').html(data);
                        }
                        else{
                            //if we got to this point we know that the controller
                            //did not return a json_encoded array. We can assume that           
                            //an unexpected PHP error occured
                            alert("An unexpeded error occured.");

                            //if you want to print the error:
                            $('#output').html(data);
                        }
                    }


                });

                if (!confirm("Are you sure about this change?")) {
                    revertFunc();
                }

            },
            dayClick: dayClickFunction,
            eventClick: function(calEvent, jsEvent, view) {
               id = calEvent.id;
                $('#deleteBtn').show();
                $('#updateBtn').show();
                $('#save').hide();
                $('#myModalLabel').val('Modifier une entrée');
                $('#calendar_form_title').attr('value', calEvent.title);
                var pickedDate = new Date(calEvent.start);
                if( calEvent.end == null )
                {

                    $('#calendar_form_end_day').val(pickedDate.getDate());
                    $('#calendar_form_end_month').val(pickedDate.getMonth()+1);
                    $('#fcalendar_form_end_year').val(pickedDate.getFullYear());
                }
                else
                { 
                    var Enddate = new Date(calEvent.end);
                    $('#calendar_form_end_day').val(Enddate.getDate());
                    $('#calendar_form_end_month').val(Enddate.getMonth()+1);
                    $('#fcalendar_form_end_year').val(Enddate.getFullYear());
                }
                
                $('#divTitle').html('Start date '+pickedDate.getDate()+'/'+(pickedDate.getMonth()+1)+'/'+pickedDate.getFullYear());
                $.ajaxSetup({
                    'type': 'POST',
                    'error':function(data,textStatus, xhr){
                        if(xhr.status == 500){//bad request
                            alert("An error occured"+textStatus);
                        }
                    }
                });
                $("#myModal").modal();
            }
        });
    };
}) ();