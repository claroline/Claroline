import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool/containers/page'
import {WorkspaceEvaluation} from '#/main/evaluation/workspace/components/evaluation'

const ProgressionTool = (props) =>
  <ToolPage>
    <WorkspaceEvaluation
      workspaceEvaluation={props.workspaceEvaluation}
      resourceEvaluations={props.resourceEvaluations}
    />
  </ToolPage>

ProgressionTool.propTypes = {
  workspaceEvaluation: T.object,
  resourceEvaluations: T.array
}

export {
  ProgressionTool
}
