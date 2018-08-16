import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {
  SUMMARY_PIN_TOGGLE,
  SUMMARY_OPEN_TOGGLE,
  STEP_ENABLE_NAVIGATION,
  STEP_DISABLE_NAVIGATION,
  STEP_UPDATE_PROGRESSION
} from '#/plugin/path/resources/path/store/actions'

import {reducer as editorReducer} from '#/plugin/path/resources/path/editor/store/reducer'
import {getStepPath} from '#/plugin/path/resources/path/editor/utils'

const reducer = combineReducers({
  summary: combineReducers({
    pinned: makeReducer(false, {
      [RESOURCE_LOAD]: (state, action) => get(action.resourceData, 'path.display.openSummary') || state,
      [SUMMARY_PIN_TOGGLE]: (state) => !state
    }),
    opened: makeReducer(false, {
      [RESOURCE_LOAD]: (state, action) => get(action.resourceData, 'path.display.openSummary') || state,
      [SUMMARY_OPEN_TOGGLE]: (state) => !state
    })
  }),
  navigationEnabled: makeReducer(true, {
    [STEP_ENABLE_NAVIGATION]: () => true,
    [STEP_DISABLE_NAVIGATION]: () => false
  }),
  pathForm: editorReducer.pathForm,
  path: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.path || state,
    // replaces path data after success updates
    [FORM_SUBMIT_SUCCESS+'/pathForm']: (state, action) => action.updatedData,
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
  })
})

export {
  reducer
}
