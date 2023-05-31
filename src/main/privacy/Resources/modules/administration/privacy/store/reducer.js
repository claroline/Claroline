import { makeReducer } from '#/main/app/store/reducer'
import { makeInstanceAction } from '#/main/app/store/actions'
import {combineReducers} from '#/main/app/store/reducer'

import { selectors } from '#/main/privacy/administration/privacy/store/selectors'
import { TOOL_LOAD } from '#/main/core/tool/store'

const reducer = makeReducer({
  [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.parameters
})

export {
  reducer
}