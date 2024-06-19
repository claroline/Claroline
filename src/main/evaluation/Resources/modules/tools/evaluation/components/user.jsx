import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {route} from '#/main/community/user/routing'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool'

import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {WorkspaceEvaluation} from '#/main/evaluation/workspace/components/evaluation'
import {constants as baseConstants} from '#/main/evaluation/constants'
import {EvaluationGauge} from '#/main/evaluation/components/gauge'

const EvaluationUser = (props) =>
  <ToolPage
    size="xl"
    breadcrumb={[
      {
        label: trans('users'),
        target: props.path + '/users'
      }
    ]}
    icon={
      <EvaluationGauge
        {...(props.workspaceEvaluation || {})}
      />
    }
    title={(
      <>
        {/*<UserAvatar user={!isEmpty(get(props.workspaceEvaluation, 'user')) ? get(props.workspaceEvaluation, 'user') : undefined} size="sm" className="me-3" />*/}
        {get(props.workspaceEvaluation, 'user.name')}
      </>
    )}
    primaryAction="show-profile"
    actions={get(props.workspaceEvaluation, 'user') ? [
      {
        name: 'show-profile',
        type: LINK_BUTTON,
        label: trans('show_profile', {}, 'actions'),
        target: route(get(props.workspaceEvaluation, 'user'), props.contextPath+'/community')
      }, {
        name: 'download-certificate',
        type: URL_BUTTON,
        label: trans('download_certificate', {}, 'actions'),
        target: ['apiv2_workspace_download_user_certificate', {
          workspace: get(props.workspaceEvaluation, 'workspace.id'),
          user: get(props.workspaceEvaluation, 'user.id')
        }],
        displayed: [
          baseConstants.EVALUATION_STATUS_COMPLETED,
          baseConstants.EVALUATION_STATUS_PARTICIPATED,
          baseConstants.EVALUATION_STATUS_PASSED
        ].includes(get(props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)),
        size: 'lg'
      }, {
        name: 'regenerate-certificate',
        type: URL_BUTTON,
        label: trans('regenerate_certificate', {}, 'actions'),
        target: ['apiv2_workspace_generate_user_certificate', {
          evaluation: [get(props.workspaceEvaluation, 'id')]
        }],
        displayed: [
          baseConstants.EVALUATION_STATUS_COMPLETED,
          baseConstants.EVALUATION_STATUS_PARTICIPATED,
          baseConstants.EVALUATION_STATUS_PASSED
        ].includes(get(props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)),
        size: 'lg'
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

    {props.loaded &&
      <WorkspaceEvaluation
        workspaceEvaluation={props.workspaceEvaluation}
        resourceEvaluations={props.resourceEvaluations}
      />
    }
  </ToolPage>

EvaluationUser.propTypes = {
  path: T.string,
  contextPath: T.string,

  // from store
  loaded: T.bool.isRequired,
  workspaceEvaluation: T.shape(
    WorkspaceEvaluationTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  ))
}

export {
  EvaluationUser
}
