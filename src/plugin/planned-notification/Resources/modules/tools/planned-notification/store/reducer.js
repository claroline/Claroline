import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {reducer as notificationsReducer} from '#/plugin/planned-notification/tools/planned-notification/notification/reducer'
import {reducer as messagesReducer} from '#/plugin/planned-notification/tools/planned-notification/message/reducer'

const reducer = combineReducers({
  canEdit: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, 'claroline_planned_notification_tool')]: (state, action) => action.toolData.canEdit
  }),
  workspace: makeReducer({}),
  notifications: notificationsReducer,
  messages: messagesReducer
})

export {
  reducer
}