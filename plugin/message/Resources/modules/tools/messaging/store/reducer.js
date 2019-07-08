import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/message/tools/messaging/store/selectors'
import {MESSAGE_LOAD, IS_REPLY} from '#/plugin/message/tools/messaging/store/actions'

const reducer = combineReducers({
  contacts: makeListReducer(`${selectors.STORE_NAME}.contacts`),

  receivedMessages: makeListReducer(`${selectors.STORE_NAME}.receivedMessages`),
  sentMessages: makeListReducer(`${selectors.STORE_NAME}.sentMessages`),
  deletedMessages: makeListReducer(`${selectors.STORE_NAME}.deletedMessages`),

  messageForm : makeFormReducer(`${selectors.STORE_NAME}.messageForm`, {}, {
    reply: makeReducer(false, {
      [IS_REPLY]: () => true
    })
  }),
  currentMessage: makeReducer({}, {
    [MESSAGE_LOAD]: (state, action) => action.message
  })
})

export {
  reducer
}
