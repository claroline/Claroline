import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

export const ACTIONS_CHART_LOAD = 'ACTIONS_CHART_LOAD'

export const actions = {}

actions.loadActions = makeActionCreator(ACTIONS_CHART_LOAD, 'data')
actions.fetchActions = (url) => ({
  [API_REQUEST]: {
    url: url,
    success: (response, dispatch) => dispatch(actions.loadActions(response))
  }
})
