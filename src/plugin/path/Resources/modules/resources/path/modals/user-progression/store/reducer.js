import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {USER_STEPS_PROGRESSION_LOAD} from '#/plugin/path/resources/path/modals/user-progression/store/actions'

const reducer = combineReducers({
  progression: makeReducer({}, {
    [USER_STEPS_PROGRESSION_LOAD]: (state, action) => action.progression
  }),
  lastAttempt: makeReducer(null, {
    [USER_STEPS_PROGRESSION_LOAD]: (state, action) => action.lastAttempt
  }),
  resourceEvaluations: makeReducer([], {
    [USER_STEPS_PROGRESSION_LOAD]: (state, action) => action.resourceEvaluations
  })
})

export {
  reducer
}
