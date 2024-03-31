import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/notification/account/notifications/store/selectors'

const reducer = combineReducers({
  notifications: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'notification.meta.created', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  })
})

export {
  reducer
}
