import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

import {AgendaViewWeek} from '#/plugin/agenda/tools/agenda/views/week/components/view'

export default {
  label: trans('Semaine'),
  component: AgendaViewWeek,

  display: (referenceDate) => referenceDate.format('MMMM YYYY'),

  range: (referenceDate) => [
    moment(referenceDate).startOf('week'),
    moment(referenceDate).endOf('week')
  ],

  previous: (date) => moment(date).subtract(1, 'week'),
  next: (date) => moment(date).add(1, 'week')
}
