import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {currentUser} from '#/main/core/user/current'
import {MESSAGE_LOAD, IS_REPLY, MAIL_NOTIFICATION_UPDATE} from '#/plugin/message/actions'

const authenticatedUser = currentUser()

const reducer = {
  receivedMessages: makeListReducer('receivedMessages'),
  sentMessages: makeListReducer('sentMessages'),
  deletedMessages: makeListReducer('deletedMessages'),
  messagesParameters: makeFormReducer('messagesParameters'),
  mailNotified: makeReducer(authenticatedUser.meta.mailNotified, {
    [MAIL_NOTIFICATION_UPDATE]: (state, action) => action.notified
  }),
  messageForm : makeFormReducer('messageForm', {
    reply: false
  }, {
    reply: makeReducer(false, {
      [IS_REPLY]: () => true
    })
  }),
  currentMessage: makeReducer({}, {
    [MESSAGE_LOAD]: (state, action) => action.message
  })
}

export {
  reducer
}
