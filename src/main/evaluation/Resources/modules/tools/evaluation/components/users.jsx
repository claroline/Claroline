import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'
import {constants} from '#/main/evaluation/constants'
import {WorkspaceCard} from '#/main/evaluation/workspace/components/card'
import {selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationUsers = (props) =>
  <ToolPage
    subtitle={trans('users_progression', {}, 'evaluation')}
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
          label: trans('user_disabled', {}, 'community'),
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
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('send-message', {}, 'actions'),
          scope: ['object', 'collection'],
          modal: [MODAL_MESSAGE, {
            receivers: {users: rows.map((row => row.user))}
          }]
        }, {
          name: 'download-participation-certificate',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          label: trans('download_participation_certificate', {}, 'actions'),
          displayed: -1 !== rows.findIndex(row => [
            constants.EVALUATION_STATUS_COMPLETED,
            constants.EVALUATION_STATUS_PARTICIPATED
          ].includes(get(row, 'status', constants.EVALUATION_STATUS_UNKNOWN))),
          callback: () => {
            rows.map(row => {
              if ([
                constants.EVALUATION_STATUS_COMPLETED,
                constants.EVALUATION_STATUS_PARTICIPATED
              ].includes(get(row, 'status', constants.EVALUATION_STATUS_UNKNOWN))) {
                props.downloadParticipationCertificate(row)
              }
            })
          },
          group: trans('transfer'),
          scope: ['object', 'collection']
        }, {
          name: 'download-success-certificate',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          label: trans('download_success_certificate', {}, 'actions'),
          displayed: -1 !== rows.findIndex((row) => constants.EVALUATION_STATUS_PASSED === get(row, 'status', constants.EVALUATION_STATUS_UNKNOWN)),
          callback: () => {
            rows.map(row => {
              if (constants.EVALUATION_STATUS_PASSED === get(row, 'status', constants.EVALUATION_STATUS_UNKNOWN)) {
                props.downloadSuccessCertificate(row)
              }
            })
          },
          group: trans('transfer'),
          scope: ['object', 'collection']
        }
      ]}
      card={WorkspaceCard}
    />
  </ToolPage>

EvaluationUsers.propTypes = {
  path: T.string.isRequired,
  contextId: T.string.isRequired,
  downloadParticipationCertificate: T.func.isRequired,
  downloadSuccessCertificate: T.func.isRequired
}

export {
  EvaluationUsers
}
