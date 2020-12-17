import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

import {AgendaViewList} from '#/plugin/agenda/tools/agenda/views/list/containers/view'

// TODO : find a way to manage it like other view modes

export default {
  autoload: false,
  label: trans('agenda_list', {}, 'agenda'),
  component: AgendaViewList,

  display: () => trans('all_events', {}, 'agenda'),

  range: () => [
    null,
    null
  ],

  previous: (date) => moment(date), // returning same date will disable the LINK_BUTTON
  next: (date) => moment(date)
}
