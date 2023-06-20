import {TOOL_LOAD} from '#/main/core/tool/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {selectors as paramSelectors} from '#/main/core/administration/parameters/store/selectors'

export const reducer = combineReducers({
  passwordValidate: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, paramSelectors.STORE_NAME)]: (state, action) => action.toolData.authenticationParameters
  })
})
