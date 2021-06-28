import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'
import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

// actions
export const ADMINISTRATION_LOAD = 'ADMINISTRATION_LOAD'

// action creators
export const actions = {}

actions.load = makeActionCreator(ADMINISTRATION_LOAD, 'tools')

/**
 * Fetch the required data to open administration.
 */
actions.open = () => (dispatch, getState) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_admin_open'],
    success: (response) => {
      dispatch(actions.load(response.tools))

      // set menu state based on a admin configuration
      // TODO : get the value from the open response later
      dispatch(menuActions.setState(configSelectors.param(getState(), 'admin.menu', null)))
    }
  }
})
