import {makeReducer} from '#/main/app/store/reducer'

import {USER_STEPS_PROGRESSION_LOAD} from '#/plugin/path/resources/path/modals/user-progression/store/actions'

const reducer = makeReducer({}, {
  [USER_STEPS_PROGRESSION_LOAD]: (state, action) => action.stepsProgression
})

export {
  reducer
}
