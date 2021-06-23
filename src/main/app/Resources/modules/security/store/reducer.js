import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  SECURITY_USER_CHANGE,
  SECURITY_USER_UPDATE
} from '#/main/app/security/store/actions'

export const reducer = combineReducers({
  currentUser: makeReducer(null, {
    [SECURITY_USER_CHANGE]: (state, action) => action.user,
    [SECURITY_USER_UPDATE]: (state, action) => action.user
  }),
  impersonated: makeReducer(false, {
    [SECURITY_USER_CHANGE]: (state, action) => action.impersonated
  }),
  administration: makeReducer(false, {
    [SECURITY_USER_CHANGE]: (state, action) => action.administration
  }),
  client: makeReducer({ip: null, forwarded: null})
})
