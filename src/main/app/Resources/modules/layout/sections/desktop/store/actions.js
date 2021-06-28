import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'
import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

// actions
export const DESKTOP_LOAD = 'DESKTOP_LOAD'

// action creators
export const actions = {}

actions.load = makeActionCreator(DESKTOP_LOAD, 'data')

/**
 * Fetch the required data to open the current user desktop.
 */
actions.open = () => (dispatch, getState) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_desktop_open'],
    success: (response) => {
      dispatch(actions.load(response))

      // set menu state based on desktop configuration
      // TODO : get the value from the open response later
      dispatch(menuActions.setState(configSelectors.param(getState(), 'desktop.menu', null)))
    },
    error: (error, errorStatus) => {
      if (403 === errorStatus) {
        dispatch(actions.load({}))
      }
    }
  }
})
