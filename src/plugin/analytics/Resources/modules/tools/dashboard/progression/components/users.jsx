import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants} from '#/main/core/workspace/constants'

import {selectors} from '#/plugin/analytics/tools/dashboard/progression/store'

const ProgressionUsers = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.evaluations'}
    fetch={{
      url: ['apiv2_workspace_evaluations_list', {workspace: props.workspaceId}],
      autoload: true
    }}
    definition={[
      {
        name: 'user',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
        name: 'date',
        type: 'date',
        label: trans('date'),
        options: {
          time: true
        },
        displayed: true
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
        type: 'progression',
        label: trans('progression'),
        displayed: true,
        filterable: false,
        calculated: (row) => ((row.progression || 0) / (row.progressionMax || 1)) * 100,
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
              current: (row.score / row.scoreMax) * 100,
              total: 100
            }
          }

          return null
        },
        displayed: true,
        filterable: false
      }
    ]}
    actions={(rows) => [{
      name: 'open',
      icon: 'fa fa-fw fa-eye',
      label: trans('open', {}, 'actions'),
      type: LINK_BUTTON,
      target: `${props.path}/progression/${get(rows[0], 'user.id')}`,
      displayed: !!get(rows[0], 'user.id'),
      scope: ['object']
    }]}
    selectable={false}
  />

ProgressionUsers.propTypes = {
  path: T.string.isRequired,
  workspaceId: T.string.isRequired
}

export {
  ProgressionUsers
}
