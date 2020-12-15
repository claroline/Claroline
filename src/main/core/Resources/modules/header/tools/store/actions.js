import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const USER_TOOLS_LOAD       = 'USER_TOOLS_LOAD'
export const USER_TOOLS_SET_LOADED = 'USER_TOOLS_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeActionCreator(USER_TOOLS_LOAD, 'tools')
actions.setLoaded = makeActionCreator(USER_TOOLS_SET_LOADED, 'loaded')

actions.getTools = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_desktop_tools'],
    before: (dispatch) => dispatch(actions.setLoaded(false)),
    success: (response, dispatch) => dispatch(actions.load(response))
  }
})
