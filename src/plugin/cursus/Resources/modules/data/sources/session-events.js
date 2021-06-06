import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {EventCard} from '#/plugin/cursus/event/components/card'

export default {
  name: 'session-events',
  icon: 'fa fa-fw fa-cubes',
  parameters: {
    primaryAction: (event) => ({
      type: URL_BUTTON,
      target: '#'
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: false
      }, {
        name: 'description',
        type: 'string',
        label: trans('description'),
        displayed: true
      }, {
        name: 'meta.type',
        type: 'string',
        label: trans('event_type'),
        displayed: false
      }, {
        name: 'restrictions.users',
        alias: 'maxUsers',
        type: 'number',
        label: trans('max_participants', {}, 'cursus'),
        displayed: true
      }, {
        name: 'restrictions.dates[0]',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'restrictions.dates[1]',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }
    ],
    card: EventCard
  }
}
