import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

// don't load it from the `api` barrel to avoid circular dependency
import {constants as apiConst} from '#/main/app/api/constants'

import {makeActionCreator} from '#/main/app/store/actions'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_CONNECTION} from '#/main/app/modals/connection'
import {selectors} from '#/main/app/security/store/selectors'

// actions
export const SECURITY_USER_CHANGE = 'SECURITY_USER_CHANGE'
export const SECURITY_USER_UPDATE = 'SECURITY_USER_UPDATE'

// action creators
export const actions = {}

actions.updateUser = makeActionCreator(SECURITY_USER_UPDATE, 'user')

actions.changeUser = (user, impersonated = false, administration = false) => (dispatch, getState) => {
  // we will dispatch action only if the user has really changed
  // this will avoid false positive as it is used by other ui components
  // to know when to invalidate/reload data for the new user
  const currentUser = selectors.currentUser(getState())
  if (get(currentUser, 'id') !== get(user, 'id')) {
    dispatch({
      type: SECURITY_USER_CHANGE,
      user: user,
      impersonated: impersonated,
      administration: administration
    })
  }
}

actions.login = (username, password, rememberMe) => ({
  [apiConst.API_REQUEST]: {
    silent: true,
    forceReauthenticate: false,
    url: ['claro_security_login'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        username: username,
        password: password,
        remember_me: rememberMe
      })
    },
    success: (response, dispatch) => dispatch(actions.onLogin(response))
  }
})

actions.onLogin = (response) => (dispatch) => {
  dispatch(actions.changeUser(response.user, false, response.administration))

  if (!isEmpty(response.messages)) {
    dispatch(modalActions.showModal(MODAL_CONNECTION, {
      messages: response.messages
    }))
  }
}

actions.logout = () => ({
  [apiConst.API_REQUEST]: {
    silent: true,
    url: ['claro_security_logout'],
    success: (response, dispatch) => dispatch(actions.changeUser(null, false, false))
  }
})

actions.linkExternalAccount = (service, username, onSuccess) => ({
  [apiConst.API_REQUEST]: {
    url: ['claro_oauth_link_account', {service: service, username: username}],
    request: {
      method: 'POST'
    },
    success: onSuccess
  }
})
