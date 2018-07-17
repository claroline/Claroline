import {makeReducer} from '#/main/app/store/reducer'

import {reducer as notificationsReducer} from '#/plugin/planned-notification/tools/planned-notification/notification/reducer'
import {reducer as messagesReducer} from '#/plugin/planned-notification/tools/planned-notification/message/reducer'

const reducer = {
  canEdit: makeReducer({}, {}),
  workspace: makeReducer({}, {}),
  notifications: notificationsReducer,
  messages: messagesReducer
}

export {
  reducer
}