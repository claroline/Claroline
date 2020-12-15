import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST, url} from '#/main/app/api'

export const ACTIONS_CHART_LOAD = 'ACTIONS_CHART_LOAD'

export const actions = {}

actions.loadActions = makeActionCreator(ACTIONS_CHART_LOAD, 'data')
actions.fetchActions = (fetchUrl, start, end) => {
  const filters = {}
  if (start) {
    filters.dateLog = start
  }
  if (end) {
    filters.dateTo = end
  }

  return ({
    [API_REQUEST]: {
      url: url(fetchUrl, {filters: filters}),
      success: (response, dispatch) => dispatch(actions.loadActions(response))
    }
  })
}
