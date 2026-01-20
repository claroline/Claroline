import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {URL_BUTTON} from '#/main/app/buttons'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {SessionCourseCard} from '#/plugin/cursus/session/components/card'
import {EventStatus} from '#/plugin/cursus/components/event-status'

export default {
  name: 'my-sessions',
  icon: 'fa fa-fw fa-calendar-week',
  parameters: {
    primaryAction: (session) => ({
      type: URL_BUTTON,
      target: session.workspace ? `#${workspaceRoute(session.workspace)}` : '',
      disabled: !session.workspace
    }),
    definition: [
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        displayed: true,
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
        render: (row) =>
          <EventStatus
            startDate={get(row, 'dates[0]')}
            endDate={get(row, 'dates[1]')}
          />
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
        name: 'course',
        type: 'training_course',
        label: trans('course', {}, 'cursus'),
        displayed: true
      }, {
        name: 'location',
        type: 'location',
        label: trans('location'),
        placeholder: trans('online_session', {}, 'cursus'),
        displayed: true,
        options: {multiple: false}
      }, {
        name: 'dates[0]',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'dates[1]',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        sortable: false
      }, {
        name: 'courseTags',
        type: 'tag',
        label: trans('tags'),
        displayed: false,
        displayable: false,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CursusBundle\\Entity\\Course'
        }
      }
    ],
    card: SessionCourseCard
  }
}
