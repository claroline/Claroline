import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {makeLogReducer} from '#/main/core/layout/logs/reducer'
import {LOAD_ANALYTICS} from '#/main/core/tools/dashboard/store/actions'

const reducer = makeLogReducer({}, {
  analytics: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_ANALYTICS] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_ANALYTICS]: (state, action) => action.data
    })
  }),
  connections: combineReducers({
    list: makeListReducer('connections.list', {
      sortBy: {property: 'connectionDate', direction: -1}
    })
  })
})

export {
  reducer
}
