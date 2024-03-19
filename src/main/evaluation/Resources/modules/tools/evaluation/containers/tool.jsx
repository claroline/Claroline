import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationTool as EvaluationToolComponent} from '#/main/evaluation/tools/evaluation/components/tool'
import {actions} from '#/main/evaluation/tools/evaluation/store'

const EvaluationTool = connect(
  (state) => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canShowEvaluations: hasPermission('show_evaluations', toolSelectors.toolData(state)),
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

export {
  EvaluationTool
}
