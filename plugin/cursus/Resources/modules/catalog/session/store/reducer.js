import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_SESSION_USER, LOAD_SESSION_QUEUE} from '#/plugin/cursus/catalog/session/store/actions'

const reducer = {
  session: makeReducer(),
  sessionUser: makeReducer(null, {
    [LOAD_SESSION_USER]: (state, action) => action.sessionUser
  }),
  sessionQueue: makeReducer(null, {
    [LOAD_SESSION_QUEUE]: (state, action) => action.sessionQueue
  }),
  isFull: makeReducer()
}

export {
  reducer
}