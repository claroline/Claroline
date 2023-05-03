import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {
  STEP_ENABLE_NAVIGATION,
  STEP_DISABLE_NAVIGATION,
  STEP_UPDATE_PROGRESSION,
  ATTEMPT_LOAD,
  RESOURCE_EVALUATIONS_LOAD
} from '#/plugin/path/resources/path/store/actions'

import {reducer as editorReducer} from '#/plugin/path/resources/path/editor/store/reducer'
import {reducer as analyticsReducer} from '#/plugin/path/analytics/resource/progression/store/reducer'
import {selectors as editorSelectors} from '#/plugin/path/resources/path/editor/store/selectors'

const reducer = combineReducers({
  navigationEnabled: makeReducer(true, {
    [STEP_ENABLE_NAVIGATION]: () => true,
    [STEP_DISABLE_NAVIGATION]: () => false
  }),
  pathForm: editorReducer,
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
  path: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.path || state,
    // replaces path data after success updates
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, editorSelectors.FORM_NAME)]: (state, action) => action.updatedData
  }),
  stepsProgression: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => !isEmpty(action.resourceData.stepsProgression) ? action.resourceData.stepsProgression : state,
    [STEP_UPDATE_PROGRESSION]: (state, action) => {
      const newState = cloneDeep(state)

      newState[action.stepId] = action.status

      return newState
    }
  }),
  analytics: analyticsReducer
})

export {
  reducer
}
