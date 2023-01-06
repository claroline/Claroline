import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/notification/account/notifications/store/selectors'

const reducer = combineReducers({
  notifications: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'notification.meta.created', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  })
})

export {
  reducer
}
