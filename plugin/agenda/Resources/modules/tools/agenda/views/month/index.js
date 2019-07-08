import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

import {AgendaViewMonth} from '#/plugin/agenda/tools/agenda/views/month/components/view'

export default {
  label: trans('Mois'),
  component: AgendaViewMonth,

  display: (referenceDate) => referenceDate.format('MMMM YYYY'),

  range: (referenceDate) => [
    moment(referenceDate).startOf('month'),
    moment(referenceDate).endOf('month')
  ],

  previous: (date) => moment(date).subtract(1, 'month'),
  next: (date) => moment(date).add(1, 'month')
}
