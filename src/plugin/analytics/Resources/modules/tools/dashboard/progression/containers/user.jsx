import {connect} from 'react-redux'

import {ProgressionUser as ProgressionUserComponent} from '#/plugin/analytics/tools/dashboard/progression/components/user'
import {actions, selectors} from '#/plugin/analytics/tools/dashboard/progression/store'

const ProgressionUser = connect(
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
)(ProgressionUserComponent)

export {
  ProgressionUser
}
