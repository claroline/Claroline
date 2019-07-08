import get from 'lodash/get'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {selectors} from '#/main/app/security/store/selectors'

// actions
export const SECURITY_USER_LOGIN  = 'SECURITY_USER_LOGIN'
export const SECURITY_USER_LOGOUT = 'SECURITY_USER_LOGOUT'
export const SECURITY_USER_CHANGE = 'SECURITY_USER_CHANGE'

// action creators
export const actions = {}

actions.loginUser = makeActionCreator(SECURITY_USER_LOGIN, 'user')
actions.logoutUser = makeActionCreator(SECURITY_USER_LOGOUT)
actions.changeUser = (user, impersonated = false) => (dispatch, getState) => {
  // we will dispatch action only if the user has really changed
  // this will avoid false positive as it is used by other ui components
  // to know when to invalidate/reload data for the new user
  const currentUser = selectors.currentUser(getState())
  if (get(currentUser, 'id') !== get(user, 'id')) {
    dispatch({
      type: SECURITY_USER_CHANGE,
      user: user,
      impersonated: impersonated
    })
  }
}

makeActionCreator(SECURITY_USER_CHANGE, 'user', 'impersonated')

actions.login = (username, password, rememberMe) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_security_login'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        username: username,
        password: password,
        remember_me: rememberMe
      })
    },
    success: (response, dispatch) => {
      dispatch(actions.loginUser(response.user))
      dispatch(actions.changeUser(response.user, false))
    }
  }
})

actions.logout = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_security_logout'],
    success: (response, dispatch) => {
      dispatch(actions.logoutUser())
      dispatch(actions.changeUser(null, false))
    }
  }
})
