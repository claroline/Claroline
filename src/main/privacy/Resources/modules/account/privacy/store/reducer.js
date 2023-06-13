import { FETCH_PRIVACY_REQUEST, FETCH_PRIVACY_SUCCESS, FETCH_PRIVACY_FAILURE } from '#/main/privacy/account/privacy/store/actions'
import { makeReducer } from '#/main/app/store/reducer'

const initialState = {
  privacyData: null,
  loading: false,
  error: null
}

export const reducer = makeReducer(initialState, {
  [FETCH_PRIVACY_REQUEST]: (state) => ({
    ...state,
    loading: true,
    error: null
  }),
  [FETCH_PRIVACY_SUCCESS]: (state, action) => ({
    ...state,
    privacyData: action.privacyData,
    loading: false
  }),
  [FETCH_PRIVACY_FAILURE]: (state, action) => ({
    ...state,
    loading: false,
    error: action.error
  })
})
