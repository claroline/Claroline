import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const TOP_RESOURCES_LOAD = 'TOP_RESOURCES_LOAD'

export const actions = {}

actions.loadTop = makeActionCreator(TOP_RESOURCES_LOAD, 'data')

actions.fetchTop = (url) => ({
  [API_REQUEST]: ({
    url: url,
    success: (response, dispatch) => dispatch(actions.loadTop(response))
  })
})