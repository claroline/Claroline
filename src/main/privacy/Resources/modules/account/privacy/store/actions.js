import {API_REQUEST} from '#/main/app/api'
import {actions as securityActions} from '#/main/app/security/store/actions'
import {makeActionCreator} from '#/main/app/store/actions'

export const ACCOUNT_PRIVACY_LOAD = 'ACCOUNT_PRIVACY_LOAD'

export const ACCOUNT_PRIVACY_UPDATE = 'ACCOUNT_PRIVACY_UPDATE';


export const actions = {}

actions.updateAccountPrivacy = makeActionCreator(ACCOUNT_PRIVACY_UPDATE, 'privacy')

actions.loadAccountPrivacy = makeActionCreator(ACCOUNT_PRIVACY_LOAD, 'privacy')

actions.fetchAccountPrivacy = () => ({
  [API_REQUEST]: {
    url: ['apiv2_privacy_get'],
    success: (response, dispatch) => dispatch(actions.loadAccountPrivacy(response))
  }
})

actions.acceptTerms = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_platform_terms_of_service_accept'],
    request: {
      method: 'PUT'
    },
    success: (response) => dispatch(securityActions.updateUser(response))
  }
})

actions.exportAccount = () => ({
  [API_REQUEST]: {
    url: ['apiv2_profile_export']
  }
})