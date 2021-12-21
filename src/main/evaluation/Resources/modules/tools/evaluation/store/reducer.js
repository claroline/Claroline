import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {USER_PROGRESSION_LOAD, USER_PROGRESSION_RESET} from '#/main/evaluation/tools/evaluation/store/actions'
import {selectors} from '#/main/evaluation/tools/evaluation/store/selectors'

const reducer = combineReducers({
  /**
   * The list of all workspace evaluations for all users.
   * It is filtered by workspace for the ws tool.
   */
  workspaceEvaluations: makeListReducer(selectors.STORE_NAME+'.workspaceEvaluations'),

  requiredResources: makeListReducer(selectors.STORE_NAME+'.requiredResources'),

  /**
   * The details information about one user evaluations.
   */
  current: combineReducers({
    loaded: makeReducer(false, {
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
