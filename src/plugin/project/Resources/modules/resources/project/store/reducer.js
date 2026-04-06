import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {MY_DEADLINE_LOAD} from '#/plugin/project/resources/project/store/actions'

const reducer = combineReducers({
  project: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.project
  }),
  mySubmissions: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.mySubmissions || []
  }),
  myCorrections: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.myCorrections || []
  }),
  myDeadline: makeReducer(null, {
    [MY_DEADLINE_LOAD]: (state, action) => action.deadline
  })
})

export {
  reducer
}
