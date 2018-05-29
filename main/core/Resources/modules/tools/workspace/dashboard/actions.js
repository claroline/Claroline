import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

export const LOAD_DASHBOARD = 'LOAD_DASHBOARD'

export const actions = {}

actions.loadDashboard = makeActionCreator(LOAD_DASHBOARD, 'data')

actions.getDashboardData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadDashboard({}))
  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: url([route, params]) + queryString,
        success: (response, dispatch) => {
          dispatch(actions.loadDashboard(response))
        },
        error: (err, dispatch) => {
          dispatch(actions.loadDashboard({}))
        }
      }
    })
  }
}