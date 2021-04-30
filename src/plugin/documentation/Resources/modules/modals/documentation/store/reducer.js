import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {DOCUMENTATION_LOAD} from '#/plugin/documentation/modals/documentation/store/actions'
import {selectors} from '#/plugin/documentation/modals/documentation/store/selectors'

export const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }),
  current: makeReducer(null, {
    [DOCUMENTATION_LOAD]: (state, action) => action.doc
  })
})
