import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

const reducer = combineReducers({
  path: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.path
  })
})

export {
  reducer
}
