import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const USER_STEPS_PROGRESSION_LOAD  = 'USER_STEPS_PROGRESSION_LOAD'

export const actions = {}

actions.loadUserStepsProgression = makeActionCreator(USER_STEPS_PROGRESSION_LOAD, 'stepsProgression')

actions.fetchUserStepsProgression = (resourceId, userId) => ({
  [API_REQUEST]: {
    url: ['innova_path_user_steps_progression_fetch', {id: resourceId, user: userId}],
    success: (data, dispatch) => {
      dispatch(actions.loadUserStepsProgression(data))
    }
  }
})

actions.resetUserStepsProgression = () => (dispatch) => {
  dispatch(actions.loadUserStepsProgression({progression: {}, lastAttempt: null}))
}
