import {TOOL_LOAD} from '#/main/core/tool/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors as paramSelectors} from '#/main/core/administration/parameters/store/selectors'
import {selectors} from '#/main/authentication/administration/password-validate/store/selectors'

export const reducer = makeFormReducer( selectors.STORE_NAME, {}, {
  data: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, paramSelectors.STORE_NAME)]: (state, action) => action.toolData.authenticationParameters
  })
} )
