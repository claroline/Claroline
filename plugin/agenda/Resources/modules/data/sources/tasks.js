import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

import {route} from '#/main/core/workspace/routing'
import {EventCard} from '#/plugin/agenda/data/components/event-card'

export default {
  name: 'tasks',
  parameters: {
    primaryAction: (task) => ({
      type: URL_BUTTON,
      target: `#${route(task.workspace, 'agenda')}`
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
        name: 'start',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'meta.isTaskDone',
        alias: 'isTaskDone',
        type: 'boolean',
        label: trans('task_done', {}, 'agenda'),
        displayed: true
      }, {
        name: 'notDoneYet',
        type: 'boolean',
        label: trans('not_done_yet'),
        displayed: false,
        displayable: false,
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
