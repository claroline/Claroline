import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const LOAD_ATTEMPTS_STATS = 'LOAD_ATTEMPTS_STATS'

export const actions = {}

actions.loadAttempts = makeActionCreator(LOAD_ATTEMPTS_STATS, 'stats')

actions.fetchAttempts = (quizId, userId = null) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['exercise_statistics_attempts', {id: quizId, userId: userId}],
    success: (data) => dispatch(actions.loadAttempts(data))
  }
})
