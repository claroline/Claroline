import { makeReducer } from '#/main/app/store/reducer'
import { ACCOUNT_PRIVACY_LOAD, ACCOUNT_PRIVACY_UPDATE } from '#/main/privacy/account/privacy/store/actions'

export const reducer = makeReducer({
  loaded: false,
  privacy: {}
}, {
  [ACCOUNT_PRIVACY_LOAD]: (state, action) => ({
    ...state,
    loaded: true,
    privacy: action.privacy
  }),
  [ACCOUNT_PRIVACY_UPDATE]: (state, action) => ({
    ...state,
    privacy: action.privacy
  })
})
