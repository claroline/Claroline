import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeInstanceAction} from '#/main/app/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'text')]: (state, action) => action.resourceData.resource
  }),
  availablePlaceholders: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, 'text')]: (state, action) => action.resourceData.placeholders
  }),
})

export {
  reducer
}