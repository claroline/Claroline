import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

const reducer = combineReducers({
  file: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.file
  })
})

export {
  reducer
}
