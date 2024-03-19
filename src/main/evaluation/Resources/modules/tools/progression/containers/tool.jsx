import {connect} from 'react-redux'

import {ProgressionTool as ProgressionToolComponent} from '#/main/evaluation/tools/progression/components/tool'
import {selectors} from '#/main/evaluation/tools/progression/store'

const ProgressionTool = connect(
  (state) => ({
    workspaceEvaluation: selectors.workspaceEvaluation(state),
    resourceEvaluations: selectors.resourceEvaluations(state)
  })
)(ProgressionToolComponent)

export {
  ProgressionTool
}
