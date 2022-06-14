import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants} from '#/main/core/workspace/constants'

import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {url} from '#/main/app/api'
import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

const EvaluationUsers = (props) =>
  <ToolPage
    subtitle={trans('users_progression', {}, 'evaluation')}
    actions={[
      {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export', {}, 'actions'),
        file: {
          url: url(['apiv2_workspace_evaluation_csv', {workspaceId: props.contextId}])+props.searchQueryString
        },
        group: trans('transfer')
      }
    ]}
  >
    <ListData
      name={selectors.STORE_NAME + '.workspaceEvaluations'}
      fetch={{
        url: props.contextId ?
          ['apiv2_workspace_evaluations_list', {workspace: props.contextId}] :
          ['apiv2_workspace_evaluations_all'],
        autoload: true
      }}
      definition={[
        {
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayable: !props.contextId,
          displayed: !props.contextId,
          filterable: false
        }, {
          name: 'workspaces',
          type: 'workspaces',
          label: trans('workspaces'),
          displayable: false,
          displayed: false,
          filterable: !props.contextId,
          sortable: false
        }, {
          name: 'user',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
          name: 'date',
          type: 'date',
          label: trans('last_activity'),
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
          name: 'userDisabled',
          label: trans('user_disabled'),
          type: 'boolean',
          displayable: false,
          sortable: false,
          filterable: true
        }
      ]}
      actions={(rows) => [
        {
          name: 'open',
          icon: 'fa fa-fw fa-eye',
          label: trans('open', {}, 'actions'),
          type: LINK_BUTTON,
          target: props.contextId ? `${props.path}/users/${get(rows[0], 'user.id')}` : `${props.path}/users/${get(rows[0], 'user.id')}/${get(rows[0], 'workspace.id')}`,
          displayed: !!get(rows[0], 'user.id'),
          scope: ['object']
        }, {
          name: 'export',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-csv', {}, 'actions'),
          target: ['apiv2_workspace_export_user_progression', {workspace: get(rows[0], 'workspace.id'), user: get(rows[0], 'user.id')}],
          group: trans('export'),
          scope: ['object']
        }, {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('send-message', {}, 'actions'),
          scope: ['object', 'collection'],
          modal: [MODAL_MESSAGE, {
            receivers: {users: rows.map((row => row.user))}
          }]
        }
      ]}
    />
  </ToolPage>

EvaluationUsers.propTypes = {
  path: T.string.isRequired,
  contextId: T.string.isRequired,
  searchQueryString: T.string
}

export {
  EvaluationUsers
}
