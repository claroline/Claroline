import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/evaluation/constants'

export default {
  name: 'resource_attempts',
  parameters: {
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
        name: 'resourceNode',
        type: 'resource',
        label: trans('resource'),
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
      }
    ]
  }
}
