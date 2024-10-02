import {makeReducer} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {FAVORITE_TOGGLE, PLATFORM_SET_CURRENT_ORGANIZATION} from '#/main/app/platform/store/actions'
import cloneDeep from 'lodash/cloneDeep'

export const reducer = {
  contexts: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contexts
  }),
  contextFavorites: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contextFavorites || [],
    [FAVORITE_TOGGLE]: (state, action) => {
      const newState = cloneDeep(state)
      const pos = newState.findIndex(f => f.id === action.favorite.id)
      if (-1 === pos) {
        newState.push(action.favorite)
      } else {
        newState.splice(pos, 1)
      }

      return newState
    }
  }),
  currentOrganization: makeReducer(null, {
    [SECURITY_USER_CHANGE]: (state, action) => action.currentOrganization || null,
    [PLATFORM_SET_CURRENT_ORGANIZATION]: (state, action) => action.organization
  }),
  availableOrganizations: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.availableOrganizations || []
  })
}
