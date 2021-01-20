import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {
  STEP_ENABLE_NAVIGATION,
  STEP_DISABLE_NAVIGATION,
  STEP_UPDATE_PROGRESSION,
  ATTEMPT_LOAD
} from '#/plugin/path/resources/path/store/actions'

import {reducer as editorReducer} from '#/plugin/path/resources/path/editor/store/reducer'
import {reducer as analyticsReducer} from '#/plugin/path/analytics/resource/progression/store/reducer'
import {selectors as editorSelectors} from '#/plugin/path/resources/path/editor/store/selectors'
import {getStepPath} from '#/plugin/path/resources/path/editor/utils'

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
  path: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'innova_path')]: (state, action) => action.resourceData.path || state,
    // replaces path data after success updates
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, editorSelectors.FORM_NAME)]: (state, action) => action.updatedData,
    [STEP_UPDATE_PROGRESSION]: (state, action) => {
      const newState = cloneDeep(state)
      const stepPath = getStepPath(action.stepId, newState.steps, 0, [])

      let step = newState.steps[stepPath[0]]

      for (let i = 1; i < stepPath.length; ++i) {
        step = step.children[stepPath[i]]
      }
      step.userProgression.status = action.status

      return newState
    }
  }),
  analytics: analyticsReducer
})

export {
  reducer
}
