import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/community/tools/community/activity/store/selectors'
import {COMMUNITY_ACTIVITY_LOAD} from '#/main/community/tools/community/activity/store/actions'

const reducer = combineReducers({
  count: makeReducer({}, {
    [COMMUNITY_ACTIVITY_LOAD]: (state, action) => action.count
  }),
  actionTypes: makeReducer([], {
    [COMMUNITY_ACTIVITY_LOAD]: (state, action) => action.actionTypes
  }),
  logs: makeListReducer(selectors.STORE_NAME + '.logs', {
    sortBy: { property: 'dateLog', direction: -1 }
  }),
  connections: makeListReducer(selectors.STORE_NAME + '.connections', {
    sortBy: {property: 'connectionDate', direction: -1}
  })
})

export {
  reducer
}
