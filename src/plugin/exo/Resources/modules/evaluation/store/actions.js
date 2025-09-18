import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const QUIZ_LOAD_ATTEMPT = 'QUIZ_LOAD_ATTEMPT'
export const QUIZ_LOAD_ATTEMPT_STATS = 'QUIZ_LOAD_ATTEMPT_STATS'

export const actions = {}

actions.setCurrentPaper = makeActionCreator(QUIZ_LOAD_ATTEMPT, 'paper')
actions.loadStatistics = makeActionCreator(QUIZ_LOAD_ATTEMPT_STATS, 'stats')

actions.loadCurrentPaper = (attemptId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['exercise_attempt_get', {attemptId: attemptId}],
    success: (data) => dispatch(actions.setCurrentPaper(data))
  }
})

actions.fetchStatistics = (resourceId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['exercise_statistics', {id: resourceId}],
    success: (data) => dispatch(actions.loadStatistics(data))
  }
})