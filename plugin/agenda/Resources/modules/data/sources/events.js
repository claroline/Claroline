import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

import {route} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {EventCard} from '#/plugin/agenda/data/components/event-card'

export default {
  name: 'events',
  parameters: {
    primaryAction: (event) => ({
      type: URL_BUTTON,
      target: `#${event.workspace ? workspaceRoute(event.workspace, 'agenda') : route('agenda')}/event/${event.id}`
    }),
    definition: [
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        primary: true
      }, {
        name: 'description',
        type: 'html',
        label: trans('description'),
        displayed: true
      }, {
        name: 'allDay',
        type: 'boolean',
        label: trans('all_day', {}, 'agenda'),
        displayed: true
      }, {
        name: 'start',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'end',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'afterToday',
        type: 'boolean',
        label: trans('after_today', {}, 'agenda'),
        displayed: false,
        filterable: true,
        sortable: false
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true,
        filterable: false,
        sortable: false
      }
    ],
    card: EventCard
  }
}
