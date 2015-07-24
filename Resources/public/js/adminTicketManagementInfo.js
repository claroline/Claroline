(function () {
    'use strict';
    
    var ticketId = $('#latest-intervention-datas-box').data('ticket-id');
    var ongoingStart = $('#latest-intervention-datas-box').data('ongoing-start');
    var previousTime = $('#latest-intervention-datas-box').data('previous-time');
    var ongoingTimer = $('#ongoing-intervention-timer');
    
    var displayOngoingTimer = function () {
        var now = Math.round(+new Date()/1000);
        var diff = now - ongoingStart;
        var minutes = (diff / 60) >> 0;
        var seconds = diff % 60;
        var totalTime = previousTime + minutes;
        
        if (seconds > 0) {
            totalTime++;
        }
        var nbHours = Math.floor(totalTime / 60);
        var nbMinutes = totalTime % 60;
        var requiredCredits = (5 * nbHours) + Math.ceil(nbMinutes / 15);
        
        var timerDisplay = minutes + ':' + (('' + seconds).length > 1 ? '' : '0') + seconds;
        timerDisplay += ' [<b>' + Translator.trans('required_credits', {}, 'support')  + ' :</b> ' + requiredCredits + ']';
        ongoingTimer.html(timerDisplay);
    };
    
    $('#latest-intervention-info').on('click', '#start-intervention-btn', function () {
        $.ajax({
            url: Routing.generate(
                'formalibre_admin_ticket_intervention_start',
                {'ticket': ticketId}
            ),
            type: 'POST',
            success: function () {
                window.location.reload();
            }
        });
    });
    
    $('#latest-intervention-info').on('click', '#stop-ongoing-intervention-btn', function () {
        var interventionId = $(this).data('intervention-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_admin_ticket_intervention_status_edit_form',
                {'intervention': interventionId}
            ),
            stopIntervention,
            function() {}
        );
    });
    
    setInterval(displayOngoingTimer, 1000);
    
    var stopIntervention = function (interventionId) {
        $.ajax({
            url: Routing.generate(
                'formalibre_admin_ticket_intervention_stop',
                {'intervention': interventionId}
            ),
            type: 'POST',
            success: function () {
                window.location.reload();
            }
        });
    };
})();