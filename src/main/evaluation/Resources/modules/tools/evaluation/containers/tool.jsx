import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationTool as EvaluationToolComponent} from '#/main/evaluation/tools/evaluation/components/tool'
import {reducer, selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      contextType: toolSelectors.contextType(state),
      canFollow: hasPermission('edit', toolSelectors.tool(state)),
      assignedSequences: selectors.assignedSequences(state)
    })
  )(EvaluationToolComponent)
)

export {
  EvaluationTool
}
