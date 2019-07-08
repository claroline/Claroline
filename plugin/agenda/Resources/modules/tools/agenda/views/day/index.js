import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

import {AgendaViewDay} from '#/plugin/agenda/tools/agenda/views/day/components/view'

export default {
  label: trans('Jour'),
  component: AgendaViewDay,

  display: (referenceDate) => referenceDate.format('MMMM YYYY'),

  range: (referenceDate) => [
    moment(referenceDate).startOf('day'),
    moment(referenceDate).endOf('day')
  ],

  previous: (date) => moment(date).subtract(1, 'day'),
  next: (date) => moment(date).add(1, 'day')
}
