import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

const reducer = combineReducers({
  bookReference: makeFormReducer('bookReference', {}, {
    data: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.bookReference
    }),
    originalData: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.bookReference
    })
  })
})

export {
  reducer
}
