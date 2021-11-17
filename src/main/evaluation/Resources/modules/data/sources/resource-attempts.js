import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/core/resource/constants'

export default {
  name: 'resource_attempts',
  parameters: {
    definition: [
      {
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
        label: trans('date'),
        type: 'date',
        options: {time: true},
        displayed: true,
        primary: true
      }, {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        options: {
          choices: constants.EVALUATION_STATUSES
        },
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
        calculated: (rowData) => rowData.progression && rowData.progressionMax ? Math.round((rowData.progression / rowData.progressionMax) * 100) : 0,
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
