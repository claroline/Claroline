import {makeReducer} from '#/main/app/store/reducer'

import {
  NOTIFICATIONS_COUNT
} from '#/plugin/notification/header/notifications/store/actions'

export const reducer = makeReducer(0, {
  [NOTIFICATIONS_COUNT]: (state, action) => action.count
})
