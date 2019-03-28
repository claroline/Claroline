import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/path/resources/path/dashboard/store/selectors'
import {USER_STEPS_PROGRESSION_LOAD} from '#/plugin/path/resources/path/dashboard/store/actions'

const reducer = combineReducers({
  evaluations: makeListReducer(selectors.STORE_NAME + '.evaluations'),
  userStepsProgression: makeReducer({}, {
    [USER_STEPS_PROGRESSION_LOAD]: (state, action) => action.stepsProgression
  })
})

export {
  reducer
}
