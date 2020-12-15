import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const HISTORY_LOAD       = 'HISTORY_LOAD'
export const HISTORY_SET_LOADED = 'HISTORY_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeActionCreator(HISTORY_LOAD, 'history')
actions.setLoaded = makeActionCreator(HISTORY_SET_LOADED, 'loaded')

actions.getHistory = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_user_history'],
    before: (dispatch) => dispatch(actions.setLoaded(false)),
    success: (response, dispatch) => dispatch(actions.load(response))
  }
})