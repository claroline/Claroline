import {makeReducer} from '#/main/app/store/reducer'
import {PRIVACY_LOAD} from '#/main/privacy/account/privacy/store/actions'

export const reducer =
  makeReducer([], {
  [PRIVACY_LOAD]: (state, action) => action.accountPrivacy
})
