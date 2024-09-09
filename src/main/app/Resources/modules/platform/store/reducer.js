import {makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {PLATFORM_SET_CURRENT_ORGANIZATION} from '#/main/app/platform/store/actions'

export const reducer = {
  contexts: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contexts
  }),
  contextFavorites: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contextFavorites || []
  }),
  currentOrganization: makeReducer(null, {
    [SECURITY_USER_CHANGE]: (state, action) => action.currentOrganization || null,
    [PLATFORM_SET_CURRENT_ORGANIZATION]: (state, action) => action.organization
  }),
  availableOrganizations: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.availableOrganizations || []
  })
}
