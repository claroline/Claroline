import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {constants} from '#/main/evaluation/constants'
import {EvaluationWorkspaceCard} from '#/main/evaluation/workspace/components/card'
import {getActions, getDefaultAction} from '#/main/evaluation/workspace/utils'
import {EvaluationStatus} from '#/main/evaluation/components/status'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'progression')
  } else {
    basePath = toolRoute('progression')
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
        label: trans('workspace', {}, 'workspace'),
        displayed: true
      }, {
        name: 'startedAt',
        label: trans('start_date'),
        type: 'date',
        options: {time: true}
      }, {
        name: 'endedAt',
        label: trans('end_date'),
        type: 'date',
        options: {time: true}
      }, {
        name: 'lastActivityAt',
        label: trans('last_activity'),
        type: 'date',
        options: {time: true},
        displayed: true
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
        name: 'user.disabled',
        label: trans('user_disabled', {}, 'community'),
        type: 'boolean',
        displayable: false,
        sortable: false,
        filterable: true
      }, {
        name: 'workspaceTags', // for retro-compatibility
        type: 'tag',
        label: trans('tags'),
        displayable: true,
        sortable: false,
        alias: 'workspace.tags',
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
      }
    ],
    card: EvaluationWorkspaceCard
  }
}
