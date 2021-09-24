import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants} from '#/main/core/workspace/constants'

import {selectors} from '#/plugin/analytics/administration/dashboard/store'

const DashboardWorkspaces = (props) =>
  <ToolPage
    subtitle={trans('workspaces')}
    toolbar="more"
    actions={[
      {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export', {}, 'actions'),
        file: {
          url: url(['apiv2_workspace_evaluation_csv'])+props.searchQueryString
        },
        group: trans('transfer')
      }
    ]}
  >
    <ListData
      name={selectors.STORE_NAME + '.evaluations'}
      fetch={{
        url: ['apiv2_workspace_evaluations_all'],
        autoload: true
      }}
      definition={[
        {
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayed: true,
          filterable: false
        }, {
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
        }, {
          name: 'workspaces',
          type: 'workspaces',
          label: trans('workspaces'),
          displayable: false,
          displayed: false,
          filterable: true,
          sortable: false
        }
      ]}
      selectable={false}
    />
  </ToolPage>

DashboardWorkspaces.propTypes = {
  searchQueryString: T.string
}

export {
  DashboardWorkspaces
}
