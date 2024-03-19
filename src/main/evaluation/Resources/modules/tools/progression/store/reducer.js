import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

import {selectors} from '#/main/evaluation/tools/progression/store/selectors'

const reducer = combineReducers({
  workspaceEvaluation: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.workspaceEvaluation
  }),
  resourceEvaluations: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.resourceEvaluations
  })
})

export {
  reducer
}
