import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

const LOAD_DASHBOARD = 'LOAD_DASHBOARD'
const LOAD_ANALYTICS = 'LOAD_ANALYTICS'

const actions = {}

actions.loadDashboard = makeActionCreator(LOAD_DASHBOARD, 'data')
actions.loadAnalytics = makeActionCreator(LOAD_ANALYTICS, 'data')

actions.getDashboardData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadDashboard({}))
  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: url([route, params]) + queryString,
        success: (response, dispatch) => {
          dispatch(actions.loadDashboard(response))
        },
        error: (err, status, dispatch) => {
          dispatch(actions.loadDashboard({}))
        }
      }
    })
  }
}

actions.getAnalyticsData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadAnalytics({}))

  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: url([route, params]) + queryString,
        success: (response, dispatch) => dispatch(actions.loadAnalytics(response)),
        error: (err, status, dispatch) => dispatch(actions.loadAnalytics({}))
      }
    })
  }
}

export {
  actions,
  LOAD_DASHBOARD,
  LOAD_ANALYTICS
}
