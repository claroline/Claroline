import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store'

import {USER_PROGRESSION_LOAD, USER_PROGRESSION_RESET} from '#/main/evaluation/tools/evaluation/store/actions'
import {selectors} from '#/main/evaluation/tools/evaluation/store/selectors'

const reducer = combineReducers({
  /**
   * The list of all workspace evaluations for all users.
   * It is filtered by workspace for the ws tool.
   */
  workspaceEvaluations: makeListReducer(selectors.STORE_NAME+'.workspaceEvaluations', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, 'evaluation')]: () => true
    })
  }),

  requiredResources: makeListReducer(selectors.STORE_NAME+'.requiredResources', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, 'evaluation')]: () => true
    })
  }),

  /**
   * The details information about one user evaluations.
   */
  current: combineReducers({
    loaded: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, 'evaluation')]: () => false,
      [USER_PROGRESSION_LOAD]: () => true,
      [USER_PROGRESSION_RESET]: () => false
    }),
    workspaceEvaluation: makeReducer(null, {
      [USER_PROGRESSION_LOAD]: (state, action) => action.workspaceEvaluation
    }),
    resourceEvaluations: makeReducer([], {
      [USER_PROGRESSION_LOAD]: (state, action) => action.resourceEvaluations
    })
  })
})

export {
  reducer
}
