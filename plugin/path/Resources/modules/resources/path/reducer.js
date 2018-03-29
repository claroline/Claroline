import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/core/scaffolding/reducer'
import {makeResourceReducer} from '#/main/core/resource/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

import {
  SUMMARY_PIN_TOGGLE,
  SUMMARY_OPEN_TOGGLE,
  STEP_UPDATE_USER_PROGRESSION_STATUS
} from '#/plugin/path/resources/path/actions'

import {reducer as editorReducer} from '#/plugin/path/resources/path/editor/reducer'
import {getStepPath} from '#/plugin/path/resources/path/editor/utils'

const reducer = makeResourceReducer({}, {
  summary: combineReducers({
    pinned: makeReducer(false, {
      [SUMMARY_PIN_TOGGLE]: (state) => !state
    }),
    opened: makeReducer(false, {
      [SUMMARY_OPEN_TOGGLE]: (state) => !state
    })
  }),
  pathForm: editorReducer.pathForm,
  resourcesPicker: editorReducer.resourcesPicker,
  path: makeReducer({}, {
    // replaces path data after success updates
    [FORM_SUBMIT_SUCCESS+'/pathForm']: (state, action) => action.updatedData,
    [STEP_UPDATE_USER_PROGRESSION_STATUS]: (state, action) => {
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
  resourceTypes: makeReducer({}, {})
})

export {
  reducer
}
