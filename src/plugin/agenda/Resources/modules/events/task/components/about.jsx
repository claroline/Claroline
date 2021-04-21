import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventAbout} from '#/plugin/agenda/event/containers/about'
import {TaskMain} from '#/plugin/agenda/events/task/containers/main'

const TaskAbout = (props) =>
  <TaskMain eventId={props.event.id}>
    {props.task &&
      <EventAbout
        event={props.task}
        sections={[
          {
            name: 'url',
            type: 'url',
            label: trans('url', {}, 'data'),
            calculated: (event) => {
              if (event.workspace) {
                return `${url(['claro_index', {}, true])}#${workspaceRoute(event.workspace, 'agenda')}/event/${event.id}`
              }

              return `${url(['claro_index', {}, true])}#${route('agenda')}/event/${event.id}`
            }
          }, {
            name: 'dates',
            type: 'date-range',
            label: trans('date'),
            calculated: (event) => [event.start || null, event.end || null],
            options: {
              time: true
            }
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }, {
            name: 'location',
            type: 'location',
            label: trans('location')
          }
        ]}
        actions={[
          {
            name: 'mark-done',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-check',
            label: trans('mark-as-done', {}, 'actions'),
            callback: () => props.markDone(props.task.id),
            displayed: hasPermission('edit', props.task) && !props.task.meta.done
          }, {
            name: 'mark-todo',
            type: CALLBACK_BUTTON,
            label: trans('mark-as-todo', {}, 'actions'),
            callback: () => props.markTodo(props.task.id),
            displayed: hasPermission('edit', props.task) && props.task.meta.done
          }
        ]}
        reload={(event) => {
          props.reload(event)
          props.open(event.id)
        }}
      />
    }
  </TaskMain>

TaskAbout.propTypes = {
  // from agenda
  event: T.shape(
    EventTypes.propTypes
  ),
  reload: T.func.isRequired,

  // from store
  task: T.object,
  open: T.func.isRequired,
  markDone: T.func.isRequired,
  markTodo: T.func.isRequired
}

export {
  TaskAbout
}
