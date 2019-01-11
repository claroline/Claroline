import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/lti/resources/lti/store/selectors'

const reducer = combineReducers({
  ltiResource: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.ltiResource || state,
    [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.ltiResourceForm']: (state, action) => action.updatedData
  }),
  ltiApps: makeReducer([], {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.ltiApps || state
  }),
  ltiResourceForm: makeFormReducer(selectors.STORE_NAME+'.ltiResourceForm')
})

export {
  reducer
}
