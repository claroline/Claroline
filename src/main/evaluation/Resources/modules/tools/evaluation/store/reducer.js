import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {TOOL_OPEN} from '#/main/core/tool/store'

import {USER_PROGRESSION_LOAD, USER_PROGRESSION_RESET} from '#/main/evaluation/tools/evaluation/store/actions'
import {selectors} from '#/main/evaluation/tools/evaluation/store/selectors'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'

const reducer = combineReducers({
  /**
   * The list of all workspace evaluations for all users.
   * It is filtered by workspace for the ws tool.
   */
  workspaceEvaluations: makeListReducer(selectors.STORE_NAME+'.workspaceEvaluations', {}, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),

  requiredResources: makeListReducer(selectors.STORE_NAME+'.requiredResources', {}, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),

  /**
   * The details information about one user evaluations.
   */
  current: combineReducers({
    loaded: makeReducer(false, {
      [TOOL_OPEN]: () => false,
      [USER_PROGRESSION_LOAD]: () => true,
      [USER_PROGRESSION_RESET]: () => false
    }),
    workspaceEvaluation: makeReducer(null, {
      [TOOL_OPEN]: () => null,
      [USER_PROGRESSION_LOAD]: (state, action) => action.workspaceEvaluation
    }),
    resourceEvaluations: makeReducer([], {
      [TOOL_OPEN]: () => [],
      [USER_PROGRESSION_LOAD]: (state, action) => action.resourceEvaluations
    })
  })
})

export {
  reducer
}
