import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const USER_STEPS_PROGRESSION_LOAD  = 'USER_STEPS_PROGRESSION_LOAD'

export const actions = {}

actions.loadUserStepsProgression = makeActionCreator(USER_STEPS_PROGRESSION_LOAD, 'progression', 'lastAttempt', 'resourceEvaluations')

actions.fetchUserStepsProgression = (resourceId, userId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['innova_path_user_steps_progression_fetch', {id: resourceId, user: userId}],
    success: (data) => dispatch(actions.loadUserStepsProgression(data.progression, data.lastAttempt, data.resourceEvaluations))
  }
})

actions.resetUserStepsProgression = () => (dispatch) => dispatch(actions.loadUserStepsProgression({}, null, []))
