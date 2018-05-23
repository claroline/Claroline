import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/core/api/actions'
import {generateUrl} from '#/main/core/api/router'

export const LOAD_LOG = 'LOAD_LOG'
export const RESET_LOG = 'RESET_LOG'
export const LOAD_CHART_DATA = 'LOAD_CHART_DATA'

export const actions = {}

actions.loadLog = makeActionCreator(LOAD_LOG, 'log')
actions.resetLog = makeActionCreator(RESET_LOG, 'log')
actions.loadChartData = makeActionCreator(LOAD_CHART_DATA, 'data')

actions.openLog = (route, params = {}) => (dispatch) => {
  dispatch(actions.resetLog({}))
  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: [route, params],
        success: (response, dispatch) => {
          dispatch(actions.loadLog(response))
        },
        error: (err, dispatch) => {
          dispatch(actions.loadLog({}))
        }
      }
    })
  } else {
    dispatch(actions.loadLog({}))
  }
}

actions.getChartData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadChartData({}))
  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: generateUrl(route, params) + queryString,
        success: (response, dispatch) => {
          dispatch(actions.loadChartData(response))
        },
        error: (err, dispatch) => {
          dispatch(actions.loadChartData({}))
        }
      }
    })
  }
}