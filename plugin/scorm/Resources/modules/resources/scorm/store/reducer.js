import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/scorm/resources/scorm/store/selectors'
import {TRACKING_UPDATE} from '#/plugin/scorm/resources/scorm/player/actions'

const reducer = combineReducers({
  scorm: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.scorm || state,
    [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.scormForm']: (state, action) => action.updatedData
  }),
  trackings: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.trackings || state,
    [TRACKING_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const scoId = action.tracking['sco']['id']
      newState[scoId] = action.tracking

      return newState
    }
  }),
  results: makeListReducer(selectors.STORE_NAME+'.results', {}),
  scormForm: makeFormReducer(selectors.STORE_NAME+'.scormForm', {})
})

export {
  reducer
}
