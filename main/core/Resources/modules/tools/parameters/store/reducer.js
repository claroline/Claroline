import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {reducer as tokenReducer} from '#/main/core/tools/parameters/token/store/reducer'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/tools/parameters/store'

const reducer = combineReducers({
  tools: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.tools
  }),
  toolsConfig: makeFormReducer(selectors.STORE_NAME+'.toolsConfig', {}, {
    originalData: makeReducer([], {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.toolsConfig
    }),
    data: makeReducer([], {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.toolsConfig
    })
  }),
  tokens: tokenReducer
})

export {
  reducer
}
