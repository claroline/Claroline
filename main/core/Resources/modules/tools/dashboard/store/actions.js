import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

const LOAD_ANALYTICS = 'LOAD_ANALYTICS'

const actions = {}

actions.loadAnalytics = makeActionCreator(LOAD_ANALYTICS, 'data')

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
  LOAD_ANALYTICS
}
