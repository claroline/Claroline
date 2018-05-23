import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/core/api/actions'
import {generateUrl} from '#/main/core/api/router'

export const LOAD_DASHBOARD = 'LOAD_DASHBOARD'

export const actions = {}

actions.loadDashboard = makeActionCreator(LOAD_DASHBOARD, 'data')

actions.getDashboardData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadDashboard({}))
  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: generateUrl(route, params) + queryString,
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