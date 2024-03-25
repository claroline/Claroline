import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool/containers/page'
import {WorkspaceEvaluation} from '#/main/evaluation/workspace/components/evaluation'
import {Alert} from '#/main/app/components/alert'

const ProgressionTool = (props) =>
  <ToolPage>
    {!props.workspaceEvaluation &&
      <Alert type="warning">
        Vous n'avez pas de progression pour cet espace.
      </Alert>
    }
    {props.workspaceEvaluation &&
      <WorkspaceEvaluation
        workspaceEvaluation={props.workspaceEvaluation}
        resourceEvaluations={props.resourceEvaluations}
      />
    }
  </ToolPage>

ProgressionTool.propTypes = {
  workspaceEvaluation: T.object,
  resourceEvaluations: T.array
}

export {
  ProgressionTool
}
