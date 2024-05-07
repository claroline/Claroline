import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store/selectors'
import {reducer as entriesReducer} from '#/plugin/claco-form/resources/claco-form/player/store'
import {reducer as statsReducer} from '#/plugin/claco-form/resources/claco-form/stats/store'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.resource || state,
  }),
  categories: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.categories || state,
  }),
  keywords: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.keywords || state,
  }),
  entries: entriesReducer,
  stats: statsReducer,
  canGeneratePdf: makeReducer(false, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.canGeneratePdf || state
  }),
  roles: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.roles || state
  }),
  myRoles: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.myRoles || state
  })
})

export {
  reducer
}