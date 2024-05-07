import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Alert} from '#/main/app/components/alert'
import {Tool} from '#/main/core/tool'
import {ToolPage} from '#/main/core/tool'

import {WorkspaceEvaluation} from '#/main/evaluation/workspace/components/evaluation'
import {ContentLoader} from '#/main/app/content/components/loader'
import get from 'lodash/get'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {constants as baseConstants} from '#/main/evaluation/constants'
import {EvaluationGauge} from '#/main/evaluation/components/gauge'

const ProgressionTool = (props) =>
  <Tool
    {...props}
  >
    <ToolPage
      root={true}
      primaryAction="start"
      size="xl"
      icon={
        <EvaluationGauge
          {...(props.workspaceEvaluation || {})}
        />
      }
      actions={get(props.workspaceEvaluation, 'user') ? [
        {
          name: 'start',
          type: CALLBACK_BUTTON,
          label: [
            baseConstants.EVALUATION_STATUS_NOT_ATTEMPTED,
            baseConstants.EVALUATION_STATUS_OPENED,
            baseConstants.EVALUATION_STATUS_UNKNOWN
          ].includes(get(props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)) ? trans('start', {}, 'actions') : trans('continue', {}, 'actions'),
          callback: () => true
        }, {
          name: 'download',
          type: URL_BUTTON,
          label: trans('download_certificate', {}, 'actions'),
          target: ['apiv2_workspace_download_participation_certificate', { // FIXME
            workspace: get(props.workspaceEvaluation, 'workspace.id'),
            user: get(props.workspaceEvaluation, 'user.id')
          }],
          displayed: [
            baseConstants.EVALUATION_STATUS_COMPLETED,
            baseConstants.EVALUATION_STATUS_PARTICIPATED,
            baseConstants.EVALUATION_STATUS_PASSED
          ].includes(get(props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)),
        }
      ] : undefined}
    >
      {!props.loaded &&
        <ContentLoader
          className="row"
          size="lg"
          description="Nous chargeons la progression..."
        />
      }

      {props.loaded && !props.workspaceEvaluation &&
        <Alert type="warning">
          Vous n'avez pas de progression pour cet espace.
        </Alert>
      }
      {props.loaded && props.workspaceEvaluation &&
        <WorkspaceEvaluation
          workspaceEvaluation={props.workspaceEvaluation}
          resourceEvaluations={props.resourceEvaluations}
        />
      }
    </ToolPage>
  </Tool>

ProgressionTool.propTypes = {
  loaded: T.bool.isRequired,
  workspaceEvaluation: T.object,
  resourceEvaluations: T.array
}

export {
  ProgressionTool
}
