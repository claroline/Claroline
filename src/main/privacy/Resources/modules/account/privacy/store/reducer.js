import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {PRIVACY_DPO_DATA_LOAD} from '#/main/privacy/account/privacy/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [PRIVACY_DPO_DATA_LOAD]: () => true
  }),
  privacyParameters: makeReducer(null, {
    [PRIVACY_DPO_DATA_LOAD]: (state, action) => action.privacyParameters
  })
})
