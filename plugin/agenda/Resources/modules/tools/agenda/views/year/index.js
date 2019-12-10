import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

import {AgendaViewYear} from '#/plugin/agenda/tools/agenda/views/year/components/view'

export default {
  autoload: true,
  label: trans('agenda_year', {}, 'agenda'),
  component: AgendaViewYear,

  display: (referenceDate) => referenceDate.format('YYYY'),

  range: (referenceDate) => [
    moment(referenceDate).startOf('year'),
    moment(referenceDate).endOf('year')
  ],

  previous: (date) => moment(date).subtract(1, 'year'),
  next: (date) => moment(date).add(1, 'year')
}
