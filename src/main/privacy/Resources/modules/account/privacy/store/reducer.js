import { PRIVACY_LOAD } from '#/main/privacy/account/privacy/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [PRIVACY_LOAD]: () => true
  }),
  privacyData: makeReducer(null, {
    [PRIVACY_LOAD]: (state, action) => action.privacyData
  })
})