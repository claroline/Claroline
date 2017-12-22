import {makeReducer} from '#/main/core/utilities/redux'
import { BOOK_REFERENCE_SET } from './actions'

const reducer = makeReducer({}, {
  [BOOK_REFERENCE_SET]: (state, action) => action.bookReference
})

export {
  reducer
}
