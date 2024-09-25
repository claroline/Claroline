import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {
  STEP_ENABLE_NAVIGATION,
  STEP_DISABLE_NAVIGATION,
  STEP_UPDATE_PROGRESSION,
  ATTEMPT_LOAD,
  RESOURCE_EVALUATIONS_LOAD
} from '#/plugin/path/resources/path/store/actions'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.resource || state,
  }),

  navigationEnabled: makeReducer(true, {
    [STEP_ENABLE_NAVIGATION]: () => true,
    [STEP_DISABLE_NAVIGATION]: () => false
  }),
  attempt: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.attempt || state,
    [ATTEMPT_LOAD]: (state, action) => action.attempt
  }),
  // the list of evaluation for the embedded required resources
  resourceEvaluations: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.resourceEvaluations || state,
    [RESOURCE_EVALUATIONS_LOAD]: (state, action) => action.resourceEvaluations || state,
    [ATTEMPT_LOAD]: (state, action) => action.resourceEvaluations || state
  }),
  stepsProgression: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => !isEmpty(action.resourceData.stepsProgression) ? action.resourceData.stepsProgression : state,
    [STEP_UPDATE_PROGRESSION]: (state, action) => {
      const newState = cloneDeep(state)

      newState[action.stepId] = action.status

      return newState
    }
  })
})

export {
  reducer
}
