import { API_REQUEST } from '#/main/app/api'
import { actions as securityActions } from '#/main/app/security/store/actions'

const PRIVACY_LOAD = 'PRIVACY_LOAD'

const actions = {}

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

actions.fetch = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_terms_of_service'],
    request: {
      method: 'GET'
    }
  }
})

export {
  actions,
  PRIVACY_LOAD
}