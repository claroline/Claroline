import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {constants} from '#/main/evaluation/constants'
import {EvaluationStatus} from '#/main/evaluation/components/status'

import {getEvaluationActions, getEvaluationDefaultAction} from '#/main/evaluation//sequence/utils'
import {EvaluationSequenceCard} from '#/main/evaluation/sequence/components/card'
import {declareDataSource} from '#/main/app/data/sources'

export default declareDataSource((contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'progression')
  } else {
    basePath = toolRoute('progression')
  }

  return {
    primaryAction: (user) => getEvaluationDefaultAction(user, refresher, basePath, currentUser),
    actions: (users) => getEvaluationActions(users, refresher, basePath, currentUser),
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
        name: 'sequence',
        type: 'sequence',
        label: trans('sequence', {}, 'evaluation'),
        displayed: true,
        options: {
          picker: {
            contextType: contextType,
            contextId: get(contextData, 'id')
          }
        }
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
        name: 'meta.archived',
        alias: 'archived',
        type: 'boolean',
        label: trans('archive'),
        displayable: false,
        filterable: true
      }
    ],
    card: EvaluationSequenceCard
  }
})
