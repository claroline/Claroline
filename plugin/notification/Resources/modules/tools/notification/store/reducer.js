import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers} from '#/main/app/store/reducer'

import {selectors} from '#/plugin/notification/tools/notification/store/selectors'

const reducer = combineReducers({
  notifications: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'notification.meta.created', direction: -1}
  })
})

export {
  reducer
}
