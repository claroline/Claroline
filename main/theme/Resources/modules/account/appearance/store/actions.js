
import {API_REQUEST} from '#/main/app/api'
import {actions as securityActions} from '#/main/app/security/store/actions'

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
