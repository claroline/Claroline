import { PRIVACY_LOAD } from '#/main/privacy/account/privacy/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store'
import {selectors} from '#/main/privacy/administration/privacy/modals/dpo/store'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [PRIVACY_LOAD]: () => true
  }),
  formData : makeFormReducer(selectors.STORE_NAME),
  privacyData: makeReducer(null, {
    [PRIVACY_LOAD]: (state, action) => action.privacyData
  })
})