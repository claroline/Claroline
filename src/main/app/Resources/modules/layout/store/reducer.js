import {makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'

export const reducer = {
  contexts: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contexts
  }),
  contextFavorites: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contextFavorites
  })
}
