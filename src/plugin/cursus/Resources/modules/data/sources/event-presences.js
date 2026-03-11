import React from 'react'
import classes from 'classnames'
import {trans} from '#/main/app/intl/translation'
import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {constants} from '#/plugin/cursus/constants'
import {PresenceCard} from '#/plugin/cursus/presence/components/card'
import {getActions, getDefaultAction} from '#/plugin/cursus/presence/utils'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (user) => getDefaultAction(user, refresher, basePath, currentUser),
    actions: (presences) => getActions(presences, refresher, basePath, currentUser),
    definition: [
      {
        name: 'user',
        type: 'user',
        label: trans('user'),
        displayed: true,
        filterable: true,
        sortable: false
      }, {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        displayed: true,
        options: {
          choices: constants.PRESENCE_STATUSES
        },
        render: (row) => {
          return (
            <span
              className={classes('badge', `text-bg-${constants.PRESENCE_STATUS_COLORS[row.status]}`)}>
              {constants.PRESENCE_STATUSES[row.status]}
            </span>)
        }
      }, {
        name: 'event',
        type: 'training_event',
        label: trans('session_event', {}, 'cursus'),
        displayed: true,
        filterable: true
      }, {
        name: 'event.session',
        type: 'training_session',
        label: trans('session', {}, 'cursus'),
        displayable: false,
        filterable: true,
        sortable: false
      }, {
        name: 'event.session.course',
        type: 'training_course',
        label: trans('course', {}, 'cursus'),
        displayable: false,
        filterable: true,
        sortable: false
      }, {
        name: 'event.start',
        type: 'date',
        label: trans('start_date'),
        displayed: true,
        filterable: false,
        sortable: true,
        options: { time: true }
      }, {
        name: 'event.end',
        type: 'date',
        label: trans('end_date'),
        filterable: false,
        sortable: true,
        options: {
          time: true
        },
        displayed: true
      },  {
        name: 'eventStatus',
        type: 'choice',
        label: trans('event_status', {}, 'presence'),
        alias: 'event.plannedObject.status',
        filterable: true,
        displayable: false,
        options: {
          choices: {
            not_started: trans('session_not_started', {}, 'cursus'),
            in_progress: trans('session_in_progress', {}, 'cursus'),
            ended: trans('session_ended', {}, 'cursus'),
            not_ended: trans('session_not_ended', {}, 'cursus')
          }
        }
      }
    ],
    card: PresenceCard
  }
}
