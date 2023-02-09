import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/evaluation/constants'
import {route} from '#/main/core/workspace/routing'
import {WorkspaceCard} from '#/main/evaluation/workspace/components/card'

export default {
  name: 'workspace_evaluations',
  parameters: {
    primaryAction: (evaluation) => ({
      type: URL_BUTTON,
      target: `#${route(evaluation.workspace)}`
    }),
    definition: [
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        options: {
          choices: constants.EVALUATION_STATUSES_SHORT
        },
        displayed: true,
        render: (row) => (
          <span className={`label label-${constants.EVALUATION_STATUS_COLOR[row.status]}`}>
            {constants.EVALUATION_STATUSES_SHORT[row.status]}
          </span>
        )
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
          type: 'user'
        }
      }, {
        name: 'score',
        type: 'score',
        label: trans('score'),
        calculated: (row) => {
          if (row.scoreMax) {
            return {
              current: row.score,
              total: row.scoreMax
            }
          }

          return null
        },
        displayed: true,
        filterable: false
      }, {
        name: 'userDisabled',
        label: trans('user_disabled', {}, 'community'),
        type: 'boolean',
        displayable: false,
        sortable: false,
        filterable: true
      }
    ],
    card: WorkspaceCard
  }
}
