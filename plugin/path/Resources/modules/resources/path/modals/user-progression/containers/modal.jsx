import {connect} from 'react-redux'

import {
  actions as dashboardActions,
  selectors as dashboardSelectors
} from '#/main/core/resource/dashboard/store'
import {UserProgressionModal as UserProgressionModalComponent} from '#/plugin/path/resources/path/modals/user-progression/components/modal'

const UserProgressionModal = connect(
  (state) => ({
    stepsProgression: dashboardSelectors.stepsProgression(state)
  }),
  (dispatch) => ({
    fetchUserStepsProgression(resourceId, userId) {
      dispatch(dashboardActions.fetchUserStepsProgression(resourceId, userId))
    },
    resetUserStepsProgression() {
      dispatch(dashboardActions.resetUserStepsProgression())
    }
  })
)(UserProgressionModalComponent)

export {
  UserProgressionModal
}
