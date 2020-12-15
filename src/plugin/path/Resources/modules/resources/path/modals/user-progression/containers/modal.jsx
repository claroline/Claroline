import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {UserProgressionModal as UserProgressionModalComponent} from '#/plugin/path/resources/path/modals/user-progression/components/modal'
import {actions, reducer, selectors} from '#/plugin/path/resources/path/modals/user-progression/store'

const UserProgressionModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      stepsProgression: selectors.stepsProgression(state)
    }),
    (dispatch) => ({
      fetchUserStepsProgression(resourceId, userId) {
        dispatch(actions.fetchUserStepsProgression(resourceId, userId))
      },
      resetUserStepsProgression() {
        dispatch(actions.resetUserStepsProgression())
      }
    })
  )(UserProgressionModalComponent)
)

export {
  UserProgressionModal
}
