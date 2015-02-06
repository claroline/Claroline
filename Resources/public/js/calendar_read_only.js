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
                    $('#calendar').fullCalendar('rerenderEvents');
                }
            }
        });

        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay',
            },
            buttonText: {
                prev: Translator.trans('prev', {}, 'agenda'),
                next: Translator.trans('next', {}, 'agenda'),
                prevYear: Translator.trans('prevYear', {}, 'agenda'),
                nextYear: Translator.trans('nextYear', {}, 'agenda'),
                today:    Translator.trans('today', {}, 'agenda'),
                month:    Translator.trans('month', {}, 'agenda'),
                week:     Translator.trans('week', {}, 'agenda'),
                day:      Translator.trans('day', {}, 'agenda')
            },
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet',
                'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août',
                'sept.', 'oct.', 'nov.', 'déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            editable: false,
            events: $('a#link').attr('href'),
            axisFormat: 'HH:mm',
            timeFormat: {
                agenda: 'H:mm{ - h:mm}'
            },
            allDayText: 'all-day',
            allDaySlot: true,
            lazyFetching : true,
        });
    };
}) ();
