import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

// actions
export const ADMINISTRATION_LOAD = 'ADMINISTRATION_LOAD'

// action creators
export const actions = {}

actions.load = makeActionCreator(ADMINISTRATION_LOAD, 'tools')

/**
 * Fetch the required data to open administration.
 */
actions.open = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_admin_open'],
    success: (response, dispatch) => dispatch(actions.load(response.tools))
  }
})
