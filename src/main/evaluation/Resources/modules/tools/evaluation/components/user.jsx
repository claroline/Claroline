import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool'

import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {WorkspaceEvaluation} from '#/main/evaluation/workspace/components/evaluation'

const EvaluationUser = (props) =>
  <ToolPage>
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
