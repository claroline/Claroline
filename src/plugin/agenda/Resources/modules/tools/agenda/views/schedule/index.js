import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

import {AgendaViewSchedule} from '#/plugin/agenda/tools/agenda/views/schedule/components/view'

export default {
  autoload: true,
  label: trans('agenda_schedule', {}, 'agenda'),
  component: AgendaViewSchedule,

  display: (referenceDate) => referenceDate.format('MMMM YYYY'),

  range: (referenceDate) => [
    moment(referenceDate).startOf('month'),
    moment(referenceDate).endOf('month')
  ],

  previous: (date) => moment(date).subtract(1, 'month'),
  next: (date) => moment(date).add(1, 'month')
}
