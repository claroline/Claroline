import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_STATISTICS = 'LOAD_STATISTICS'

export const actions = {}

actions.loadStatistics = makeActionCreator(LOAD_STATISTICS, 'stats')

actions.fetchStatistics = (quizId) => {
  return (dispatch) => {
    dispatch({
      [API_REQUEST]: {
        url: ['exercise_statistics', {id: quizId}],
        success: (data, dispatch) => {
          dispatch(actions.loadStatistics(data))
        }
      }
    })
  }
}