import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {constants} from '#/main/evaluation/constants'
import {WorkspaceCard} from '#/main/evaluation/workspace/components/card'
import {getActions, getDefaultAction} from '#/main/evaluation/workspace/utils'
import {EvaluationStatus} from '#/main/evaluation/components/status'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'evaluation')
  } else {
    basePath = toolRoute('evaluation')
  }

  return {
    primaryAction: (user) => getDefaultAction(user, refresher, basePath, currentUser),
    actions: (users) => getActions(users, refresher, basePath, currentUser),
    definition: [
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        options: {
          choices: constants.EVALUATION_STATUSES_SHORT
        },
        displayed: true,
        render: (row) => <EvaluationStatus status={row.status} />
      }, {
        name: 'user',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true
      }, {
        name: 'date',
        label: trans('last_activity'),
        type: 'date',
        options: {time: true},
        displayed: true,
        primary: true
      }, {
        name: 'duration',
        type: 'time',
        label: trans('duration'),
        displayed: true,
        filterable: false
      }, {
        name: 'progression',
        label: trans('progression'),
        type: 'progression',
        displayed: true,
        filterable: false,
        options: {
          type: 'learning'
        }
      }, {
        name: 'displayScore',
        type: 'score',
        label: trans('score'),
        displayed: true,
        filterable: false
      }, {
        name: 'userDisabled',
        label: trans('user_disabled', {}, 'community'),
        type: 'boolean',
        displayable: false,
        sortable: false,
        filterable: true
      }, {
        name: 'workspaceTags',
        type: 'tag',
        label: trans('tags'),
        displayable: true,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CoreBundle\\Entity\\Workspace\\Workspace'
        }
      }, {
        name: 'workspace.hidden',
        type: 'boolean',
        label: trans('hidden'),
        displayable: false,
        sortable: false,
        filterable: true
      }, {
        name: 'workspace.code',
        type: 'string',
        label: trans('code'),
        displayable: false,
        sortable: true,
        filterable: false
      }, {
        name: 'user.registered',
        label: trans('user_registered', {}, 'community'),
        type: 'boolean',
        displayable: false,
        sortable: false,
        filterable: true
      }
    ],
    card: WorkspaceCard
  }
}
