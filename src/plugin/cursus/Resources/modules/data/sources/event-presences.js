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
        label: trans('training_event', {}, 'data_sources'),
        displayed: true,
        filterable: true
      }
    ],
    card: PresenceCard
  }
}
