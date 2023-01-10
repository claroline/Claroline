import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationUser as EvaluationUserComponent} from '#/main/evaluation/tools/evaluation/components/user'
import {actions, selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationUser = connect(
  (state) => ({
    currentUserId: securitySelectors.currentUserId(state),
    contextPath: toolSelectors.basePath(state),
    loaded: selectors.currentLoaded(state),
    workspaceEvaluation: selectors.currentWorkspaceEvaluation(state),
    resourceEvaluations: selectors.currentResourceEvaluations(state)
  }),
  (dispatch) => ({
    load(workspaceId, userId) {
      dispatch(actions.fetchUserProgression(workspaceId, userId))
    }
  })
)(EvaluationUserComponent)

export {
  EvaluationUser
}
