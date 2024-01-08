import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {constants} from '#/main/evaluation/constants'
import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {WorkspaceCard} from '#/main/evaluation/workspace/components/card'
import {getActions, getDefaultAction} from '#/main/evaluation/workspace/utils'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EvaluationStatus} from '#/main/evaluation/components/status'

const EvaluationUsers = (props) => {
  const evaluationsRefresher = {
    add:    () => props.invalidate(),
    update: () => props.invalidate(),
    delete: () => props.invalidate()
  }

  return (
    <ToolPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans('users_progression', {}, 'evaluation'),
          target: ''
        }
      ]}
      subtitle={trans('users_progression', {}, 'evaluation')}
    >
      <ContentSizing size="full">
        <ListData
          flush={true}
          name={selectors.STORE_NAME + '.workspaceEvaluations'}
          fetch={{
            url: props.contextId ?
              ['apiv2_workspace_evaluations_list', {workspace: props.contextId}] :
              ['apiv2_workspace_evaluations_all'],
            autoload: true
          }}
          primaryAction={(row) => getDefaultAction(row, evaluationsRefresher, props.path, props.currentUser)}
          actions={(rows) => getActions(rows, evaluationsRefresher, props.path, props.currentUser)}
          definition={[
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
              displayed: true,
              primary: true
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
            }
          ]}
          card={WorkspaceCard}
        />
      </ContentSizing>
    </ToolPage>
  )
}

EvaluationUsers.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  contextId: T.string,
  invalidate: T.func.isRequired
}

export {
  EvaluationUsers
}
