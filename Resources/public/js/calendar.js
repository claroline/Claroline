(function() {
    var $calendar = $('#calendar');

    $calendar.fullCalendar({
        header: {
            left: 'title',
            center: '',
            right: 'today, prev,next'
        },
        firstDay: 1,
        fixedWeekCount: false,
        weekNumbers: true,
        eventLimit: true,
        buttonText: {
            today: trans('agenda.today')
        },
        monthNames: trans(['agenda.month.january', 'agenda.month.february', 'agenda.month.march', 'agenda.month.april', 'agenda.month.may', 'agenda.month.june', 'agenda.month.july', 'agenda.month.august', 'agenda.month.september', 'agenda.month.october', 'agenda.month.november', 'agenda.month.december']),
        monthNamesShort: trans(['agenda.month.jan', 'agenda.month.feb', 'agenda.month.mar', 'agenda.month.apr', 'agenda.month.may', 'agenda.month.ju', 'agenda.month.jul', 'agenda.month.aug', 'agenda.month.sept',  'agenda.month.oct', 'agenda.month.nov', 'agenda.month.dec']),
        dayNames: trans(['agenda.day.sunday', 'agenda.day.monday', 'agenda.day.tuesday', 'agenda.day.wednesday', 'agenda.day.thursday', 'agenda.day.friday', 'agenda.day.saturday']),
        dayNamesShort: trans(['agenda.day.sun', 'agenda.day.mon', 'agenda.day.tue', 'agenda.day.wed', 'agenda.day.thu', 'agenda.day.fri', 'agenda.day.sat']),
        weekNumberTitle: trans('agenda.week_number_title'),
        dayClick: onDayClick
    });

    function onDayClick(date)
    {
        var routing = Routing.generate('formalibre_add_reservation'),
            dateDate = moment(date).format('YYYY-MM-DD'),
            dateTime = moment().format('HH:mm');

        var onReservationFormOpen = function(html) {
            $('#reservation_form_start_date').val(dateDate);
            $('#reservation_form_start_time').val(dateTime);
        };

        Claroline.Modal.displayForm(routing, onReservationCreate, onReservationFormOpen, 'reservation-form');

    }

    function onReservationCreate()
    {

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