import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/bibliography/resources/book-reference/store/selectors'

const reducer = combineReducers({
  bookReference: makeFormReducer(selectors.STORE_NAME+'.bookReference', {}, {
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.bookReference
    }),
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.bookReference
    })
  })
})

export {
  reducer
}
