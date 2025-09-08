import {createElement} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as toolRoute} from '#/main/core/tool/routing'

import {EventCard} from '#/plugin/cursus/event/components/card'
import {EventStatus} from '#/plugin/cursus/components/event-status'

export default {
  name: 'session-events',
  icon: 'fa fa-fw fa-clock',
  parameters: {
    primaryAction: (event) => ({
      type: URL_BUTTON,
      target: get(event, 'session.workspace') ?
        '#' + workspaceRoute(get(event, 'session.workspace'), 'training_events') + '/' + event.id :
        '#' + toolRoute('trainings') + '/events/' + event.id
    }),
    definition: [
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        alias: 'plannedObject.status',
        sortable: false,
        displayed: true,
        filterable: true,
        order: 1,
        options: {
          noEmpty: true,
          choices: {
            not_started: trans('session_not_started', {}, 'cursus'),
            in_progress: trans('session_in_progress', {}, 'cursus'),
            ended: trans('session_ended', {}, 'cursus'),
            not_ended: trans('session_not_ended', {}, 'cursus')
          }
        },
        render: (row) => createElement(EventStatus, {
          startDate: get(row, 'start'),
          endDate: get(row, 'end')
        })
      }, {
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
        type: 'html',
        label: trans('description'),
        displayed: true
      }, {
        name: 'tutors',
        type: 'user',
        label: trans('tutors', {}, 'cursus'),
        options: {multiple: true}
      }, {
        name: 'restrictions.users',
        alias: 'maxUsers',
        type: 'number',
        label: trans('max_participants', {}, 'cursus'),
        displayed: true
      }, {
        name: 'start',
        alias: 'plannedObject.startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'end',
        alias: 'plannedObject.endDate',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'session',
        label: trans('session', {}, 'cursus'),
        type: 'training_session',
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayable: false,
        sortable: false
      }
    ],
    card: EventCard
  }
}
