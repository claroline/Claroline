import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/cursus/tools/cursus/store/selectors'
import {reducer as catalogReducer} from '#/plugin/cursus/tools/cursus/catalog/store/reducer'

const reducer = combineReducers({
  parameters: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters || {}
  }),
  catalog: catalogReducer
})

export {
  reducer
}