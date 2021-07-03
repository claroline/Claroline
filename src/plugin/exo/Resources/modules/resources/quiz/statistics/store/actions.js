import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_STATISTICS = 'LOAD_STATISTICS'
export const LOAD_ATTEMPTS_STATS = 'LOAD_ATTEMPTS_STATS'
export const LOAD_DOCIMOLOGY = 'LOAD_DOCIMOLOGY'

export const actions = {}

actions.loadStatistics = makeActionCreator(LOAD_STATISTICS, 'stats')

actions.fetchStatistics = (quizId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['exercise_statistics', {id: quizId}],
    success: (data) => dispatch(actions.loadStatistics(data))
  }
})

actions.loadAttempts = makeActionCreator(LOAD_ATTEMPTS_STATS, 'stats')

actions.fetchAttempts = (quizId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['exercise_statistics_attempts', {id: quizId}],
    success: (data) => dispatch(actions.loadAttempts(data))
  }
})

actions.loadDocimology = makeActionCreator(LOAD_DOCIMOLOGY, 'stats')

actions.fetchDocimology = (quizId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['exercise_statistics_docimology', {id: quizId}],
    success: (data) => dispatch(actions.loadDocimology(data))
  }
})