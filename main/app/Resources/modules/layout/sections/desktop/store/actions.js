import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const DESKTOP_LOAD = 'DESKTOP_LOAD'

// action creators
export const actions = {}

actions.load = makeActionCreator(DESKTOP_LOAD, 'data')

/**
 * Fetch the required data to open the current user desktop.
 */
actions.open = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_desktop_open'],
    success: (response, dispatch) => dispatch(actions.load(response)),
    error: (error, errorStatus, dispatch) => {
      if (403 === errorStatus) {
        dispatch(actions.load({}))
      }
    }
  }
})

