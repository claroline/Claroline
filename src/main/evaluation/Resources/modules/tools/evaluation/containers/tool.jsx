import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationTool as EvaluationToolComponent} from '#/main/evaluation/tools/evaluation/components/tool'
import {actions, reducer, selectors} from '#/main/evaluation/tools/evaluation/store'
import {withReducer} from '#/main/app/store/reducer'

const EvaluationTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      contextId: toolSelectors.contextId(state),
      contextType: toolSelectors.contextType(state),
      currentUserId: securitySelectors.currentUserId(state),
      permissions: toolSelectors.permissions(state)
    }),
    (dispatch) => ({
      openEvaluation(workspaceId, userId) {
        dispatch(actions.fetchUserProgression(workspaceId, userId))
      }
    })
  )(EvaluationToolComponent)
)

export {
  EvaluationTool
}
