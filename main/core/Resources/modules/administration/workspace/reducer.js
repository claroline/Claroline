import {makeReducer, combineReducers} from '#/main/core/utilities/redux'
import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {reducer as paginationReducer} from '#/main/core/layout/pagination/reducer'
import {makeListReducer} from '#/main/core/layout/list/reducer'

import {WORKSPACES_LOAD} from './actions'

const handlers = {
  [WORKSPACES_LOAD]: (state, action) => {
    return {
      data: action.workspaces,
      totalResults: action.total
    }
  }
}

const reducer = combineReducers({
  currentRequests: apiReducer,
  workspaces: makeReducer({
    data: [],
    totalResults: 0
  }, handlers),
  pagination: paginationReducer,
  list: makeListReducer(),
  modal: modalReducer
})

export {
  reducer
}
