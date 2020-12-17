import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

export const RESOURCES_CHART_LOAD = 'RESOURCES_CHART_LOAD'
export const RESOURCES_CHART_CHANGE_MODE = 'RESOURCES_CHART_CHANGE_MODE'

export const actions = {}

actions.changeMode = makeActionCreator(RESOURCES_CHART_CHANGE_MODE, 'mode')

actions.loadResources = makeActionCreator(RESOURCES_CHART_LOAD, 'data')
actions.fetchResources = (url) => ({
  [API_REQUEST]: {
    url: url,
    success: (response, dispatch) => dispatch(actions.loadResources(response))
  }
})
