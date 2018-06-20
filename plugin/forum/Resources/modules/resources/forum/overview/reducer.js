import {makeReducer} from '#/main/app/store/reducer'

import { LAST_MESSAGES_LOAD } from '#/plugin/forum/resources/forum/actions'

const reducer = makeReducer({}, {
  [LAST_MESSAGES_LOAD]: (state, action) => action.messages
})

export {
  reducer
}
