import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

import {TRACKING_UPDATE} from '#/plugin/scorm/resources/scorm/player/actions'
import {reducer as editorReducer} from '#/plugin/scorm/resources/scorm/editor/reducer'

const reducer = {
  scorm: makeReducer({}, {
    [FORM_SUBMIT_SUCCESS+'/scormForm']: (state, action) => action.updatedData
  }),
  trackings: makeReducer({}, {
    [TRACKING_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const scoId = action.tracking['sco']['id']
      newState[scoId] = action.tracking

      return newState
    }
  }),
  results: makeListReducer('results', {}),
  scormForm: editorReducer
}

export {
  reducer
}
