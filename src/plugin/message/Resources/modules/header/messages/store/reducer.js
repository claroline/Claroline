import {makeReducer} from '#/main/app/store/reducer'

import {
  HEADER_MESSAGES_COUNT
} from '#/plugin/message/header/messages/store/actions'

export const reducer = makeReducer(0, {
  [HEADER_MESSAGES_COUNT]: (state, action) => action.count || 0
})
