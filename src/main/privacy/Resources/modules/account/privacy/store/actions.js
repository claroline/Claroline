import {API_REQUEST} from '#/main/app/api'
import {actions as securityActions} from '#/main/app/security/store/actions'
import { makeActionCreator } from '#/main/app/store/actions'

export const PRIVACY_LOAD = 'PRIVACY_LOAD'
export const actions = {}

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

actions.loadAccountPrivacy = makeActionCreator(PRIVACY_LOAD, 'accountPrivacy')

actions.fetchAccountPrivacy = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_dpo_view'],
    request: {
      method: 'GET'
    }
  }
})
