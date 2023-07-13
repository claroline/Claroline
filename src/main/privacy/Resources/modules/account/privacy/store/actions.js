import {API_REQUEST} from '#/main/app/api'
import {actions as securityActions} from '#/main/app/security/store/actions'
import {makeActionCreator} from '#/main/app/store/actions'

export const PRIVACY_DPO_DATA_LOAD = 'PRIVACY_DPO_DATA_LOAD'

export const actions = {}

actions.load = makeActionCreator(
  PRIVACY_DPO_DATA_LOAD,
  'privacyParameters'
)

actions.fetch = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_privacy_dpo_get'],
    request: {
      method: 'GET'
    },
    silent: true,
    success: (data) => dispatch(
      actions.load(
        data.privacyParameters
      )
    )
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
