import { API_REQUEST } from '#/main/app/api'
import { actions as securityActions } from '#/main/app/security/store/actions'
import { makeActionCreator } from '#/main/app/store/actions'

export const FETCH_PRIVACY_REQUEST = 'FETCH_PRIVACY_REQUEST'
export const FETCH_PRIVACY_SUCCESS = 'FETCH_PRIVACY_SUCCESS'
export const FETCH_PRIVACY_FAILURE = 'FETCH_PRIVACY_FAILURE'
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

export const fetchPrivacyRequest = makeActionCreator(FETCH_PRIVACY_REQUEST)
export const fetchPrivacySuccess = makeActionCreator(FETCH_PRIVACY_SUCCESS, 'privacyData')
export const fetchPrivacyFailure = makeActionCreator(FETCH_PRIVACY_FAILURE, 'error')

actions.fetchAccountPrivacy = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_privacy_view'],
    onStart: () => dispatch(fetchPrivacyRequest()),
    onSuccess: (response) => dispatch(fetchPrivacySuccess(response)),
    onFailure: (error) => dispatch(fetchPrivacyFailure(error))
  }
})
