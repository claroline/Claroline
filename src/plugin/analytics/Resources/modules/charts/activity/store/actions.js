import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST, url} from '#/main/app/api'

export const ACTIVITY_CHART_LOAD = 'ACTIVITY_CHART_LOAD'

export const actions = {}

actions.loadActivity = makeActionCreator(ACTIVITY_CHART_LOAD, 'data')
actions.fetchActivity = (fetchUrl, start = null, end = null) => {
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
      silent: true,
      success: (response, dispatch) => dispatch(actions.loadActivity(response))
    }
  })
}
