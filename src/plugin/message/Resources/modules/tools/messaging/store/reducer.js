import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/message/tools/messaging/store/selectors'
import {MESSAGE_LOAD} from '#/plugin/message/tools/messaging/store/actions'

const reducer = combineReducers({
  mailNotified: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.mailNotified
  }),
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

  currentMessage: makeReducer(null, {
    [MESSAGE_LOAD]: (state, action) => action.message
  })
})

export {
  reducer
}
