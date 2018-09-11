import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {EventCard} from '#/plugin/agenda/data/components/event-card'

export default {
  name: 'events',
  parameters: {
    primaryAction: (event) => ({
      type: URL_BUTTON,
      target: ['claro_workspace_open_tool', {
        workspaceId: event.workspace.id,
        toolName: 'agenda_'
      }]
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
      }
    ],
    card: EventCard
  }
}
