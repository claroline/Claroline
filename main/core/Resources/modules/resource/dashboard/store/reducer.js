import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {makeLogReducer} from '#/main/core/layout/logs/reducer'

import {selectors} from '#/main/core/resource/dashboard/store/selectors'
import {USER_STEPS_PROGRESSION_LOAD} from '#/main/core/resource/dashboard/store/actions'

const reducer = combineReducers(makeLogReducer({}, {
  evaluations: makeListReducer(selectors.STORE_NAME + '.evaluations'),
  userStepsProgression: makeReducer({}, {
    [USER_STEPS_PROGRESSION_LOAD]: (state, action) => action.stepsProgression
  }),
  connections: makeListReducer(selectors.STORE_NAME + '.connections', {
    sortBy: {property: 'connectionDate', direction: -1}
  })
}, selectors.STORE_NAME+'.'))

export {
  reducer
}
