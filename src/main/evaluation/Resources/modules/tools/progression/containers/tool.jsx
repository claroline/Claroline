import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ProgressionTool as ProgressionToolComponent} from '#/main/evaluation/tools/progression/components/tool'
import {selectors, reducer} from '#/main/evaluation/tools/progression/store'

const ProgressionTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: toolSelectors.loaded(state),
      workspaceEvaluation: selectors.workspaceEvaluation(state),
      resourceEvaluations: selectors.resourceEvaluations(state)
    })
  )(ProgressionToolComponent)
)

export {
  ProgressionTool
}
