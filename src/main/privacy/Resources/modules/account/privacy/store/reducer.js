import { PRIVACY_LOAD } from '#/main/privacy/account/privacy/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store'
import {selectors} from '#/main/privacy/administration/privacy/modals/dpo/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

export const reducer = combineReducers({
  formData : makeFormReducer(selectors.STORE_NAME),
  parameters: makeReducer({}, {
    [makeInstanceAction(PRIVACY_LOAD, 'accountPrivacy')]: (state, action) => action.toolData.parameters
  })
})
