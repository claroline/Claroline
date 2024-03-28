import {connect} from 'react-redux'

import {ProgressionTool as ProgressionToolComponent} from '#/main/evaluation/tools/progression/components/tool'
import {selectors, reducer} from '#/main/evaluation/tools/progression/store'
import {withReducer} from '#/main/app/store/reducer'

const ProgressionTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      workspaceEvaluation: selectors.workspaceEvaluation(state),
      resourceEvaluations: selectors.resourceEvaluations(state)
    })
  )(ProgressionToolComponent)
)

export {
  ProgressionTool
}
