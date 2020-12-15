import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {LOAD_REQUIREMENTS} from '#/plugin/analytics/tools/dashboard/progression/store/actions'

import {selectors as baseSelectors} from '#/plugin/analytics/tools/dashboard/store/selectors'
import {USER_PROGRESSION_LOAD, USER_PROGRESSION_RESET} from '#/plugin/analytics/tools/dashboard/progression/store/actions'
import {selectors} from '#/plugin/analytics/tools/dashboard/progression/store/selectors'

const reducer = combineReducers({
  requirements: combineReducers({
    roles: makeListReducer(selectors.STORE_NAME + '.requirements.roles', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
      })
    }),
    users: makeListReducer(selectors.STORE_NAME + '.requirements.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
      })
    }),
    current: makeReducer(null, {
      [LOAD_REQUIREMENTS]: (state, action) => action.data
    })
  }),

  /**
   * The list of all WorkspaceEvaluations.
   */
  evaluations: makeListReducer(selectors.STORE_NAME + '.evaluations', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
    })
  }),

  /**
   * The details information about one WorkspaceEvaluation.
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
