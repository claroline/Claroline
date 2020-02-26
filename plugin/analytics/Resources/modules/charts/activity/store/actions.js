import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

export const ACTIVITY_CHART_LOAD = 'ACTIVITY_CHART_LOAD'

export const actions = {}

actions.loadActivity = makeActionCreator(ACTIVITY_CHART_LOAD, 'data')
actions.fetchActivity = (url) => ({
  [API_REQUEST]: {
    url: url,
    success: (response, dispatch) => dispatch(actions.loadActivity(response))
  }
})
