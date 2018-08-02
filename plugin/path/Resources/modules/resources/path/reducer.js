import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {
  SUMMARY_PIN_TOGGLE,
  SUMMARY_OPEN_TOGGLE
} from '#/plugin/path/resources/path/actions'
import {
  STEP_ENABLE_NAVIGATION,
  STEP_DISABLE_NAVIGATION,
  STEP_UPDATE_PROGRESSION
} from '#/plugin/path/resources/path/player/actions'

import {reducer as editorReducer} from '#/plugin/path/resources/path/editor/reducer'
import {getStepPath} from '#/plugin/path/resources/path/editor/utils'

const reducer = {
  summary: combineReducers({
    pinned: makeReducer(false, {
      [SUMMARY_PIN_TOGGLE]: (state) => !state
    }),
    opened: makeReducer(false, {
      [SUMMARY_OPEN_TOGGLE]: (state) => !state
    })
  }),
  navigationEnabled: makeReducer(true, {
    [STEP_ENABLE_NAVIGATION]: () => true,
    [STEP_DISABLE_NAVIGATION]: () => false
  }),
  pathForm: editorReducer.pathForm,
  path: makeReducer({}, {
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
}

export {
  reducer
}
