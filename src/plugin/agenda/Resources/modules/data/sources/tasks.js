import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

import {route} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {EventCard} from '#/plugin/agenda/event/components/card'

export default {
  name: 'tasks',
  parameters: {
    primaryAction: (task) => ({
      type: URL_BUTTON,
      target: `#${task.workspace ? workspaceRoute(task.workspace, 'agenda') : route('agenda')}/event/${task.id}`
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
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
        displayed: true,
        options: {time: true}
      }, {
        name: 'afterToday',
        type: 'boolean',
        label: trans('after_today'),
        displayed: false,
        displayable: false,
        filterable: true,
        sortable: false
      }, {
        name: 'meta.done',
        type: 'boolean',
        label: trans('task_done', {}, 'agenda'),
        displayed: true
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
