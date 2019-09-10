import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/message/tools/messaging/store/selectors'
import {MESSAGE_LOAD, IS_REPLY} from '#/plugin/message/tools/messaging/store/actions'

const reducer = combineReducers({
  contacts: makeListReducer(`${selectors.STORE_NAME}.contacts`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),

  receivedMessages: makeListReducer(`${selectors.STORE_NAME}.receivedMessages`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  sentMessages: makeListReducer(`${selectors.STORE_NAME}.sentMessages`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  deletedMessages: makeListReducer(`${selectors.STORE_NAME}.deletedMessages`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),

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
