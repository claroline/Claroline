import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {
  EXPLORER_SET_CURRENT
} from '#/main/core/modals/resources/store/actions'
import {selectors} from '#/main/core/modals/resources/store/selectors'

const reducer = combineReducers({
  /**
   * The resource node of the current directory.
   */
  current: makeReducer(null, {
    [EXPLORER_SET_CURRENT]: (state, action) => action.current
  }),

  /**
   * The list of resources for the current directory.
   */
  resources: makeListReducer(`${selectors.STORE_NAME}.resources`, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer([], {
      [EXPLORER_SET_CURRENT]: () => true
    }),
    selected: makeReducer([], {
      [EXPLORER_SET_CURRENT]: () => []
    }),
    filters: makeReducer([], {
      [EXPLORER_SET_CURRENT]: (state, action) => action.filters
    }),
    page: makeReducer([], {
      [EXPLORER_SET_CURRENT]: () => 0
    })
  })
})

export {
  reducer
}
