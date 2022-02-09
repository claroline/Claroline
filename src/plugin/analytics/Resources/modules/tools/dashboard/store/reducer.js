import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/analytics/tools/dashboard/store/selectors'
import {reducer as pathReducer} from '#/plugin/analytics/tools/dashboard/path/store/reducer'

const reducer = combineReducers({
  count: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.count
  }),

  path: pathReducer
})

export {
  reducer
}
