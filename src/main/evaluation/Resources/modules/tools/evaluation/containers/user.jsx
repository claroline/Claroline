import {connect} from 'react-redux'

import {EvaluationUser as EvaluationUserComponent} from '#/main/evaluation/tools/evaluation/components/user'
import {actions, selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationUser = connect(
  (state) => ({
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
