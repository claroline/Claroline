import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/evaluation/constants'
import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {WorkspaceCard} from '#/main/evaluation/workspace/components/card'
import {getActions, getDefaultAction} from '#/main/evaluation/workspace/utils'
import {EvaluationStatus} from '#/main/evaluation/components/status'
import {EvaluationScore} from '#/main/evaluation/components/score'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {PageListSection} from '#/main/app/page/components/list-section'

const EvaluationUsers = (props) => {
  const evaluationsRefresher = {
    add:    () => props.invalidate(),
    update: () => props.invalidate(),
    delete: () => props.invalidate()
  }

  return (
    <ToolPage
      title={trans('users')}
    >
      <PageListSection>
        <ListData
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
              displayed: props.hasScore,
              displayable: props.hasScore,
              placeholder: props.hasScore && (
                <div className="d-inline-flex gap-2 flex-row align-items-center" role="presentation">
                  <TooltipOverlay
                    id="score-help"
                    tip={trans('Le score est calculé une fois que l\'utilisateur a terminé toutes les activités à faire.', {}, 'evaluation')}
                    position="left"
                  >
                    <span className="fa fa-fw fa-info-circle cursor-help fs-sm text-body-tertiary" />
                  </TooltipOverlay>

                  <EvaluationScore scoreMax={props.totalScore} />
                </div>
              ),
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
      </PageListSection>
    </ToolPage>
  )
}

EvaluationUsers.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  contextId: T.string,
  hasScore: T.bool.isRequired,
  totalScore: T.number,
  invalidate: T.func.isRequired
}

export {
  EvaluationUsers
}
