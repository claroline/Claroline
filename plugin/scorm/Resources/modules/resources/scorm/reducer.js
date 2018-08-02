import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

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
