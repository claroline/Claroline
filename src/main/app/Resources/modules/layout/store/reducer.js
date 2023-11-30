import {makeReducer} from '#/main/app/store/reducer'

import {MAINTENANCE_SET} from '#/main/app/layout/store/actions'

// menu
import {reducer as menuReducer} from '#/main/app/layout/menu/store/reducer'
import {selectors as menuSelectors} from '#/main/app/layout/menu/store/selectors'
import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'

export const reducer = {
  contexts: makeReducer([], {
    [SECURITY_USER_CHANGE]: (state, action) => action.contexts
  }),
  maintenance: makeReducer({enabled: false, message: null}, {
    [MAINTENANCE_SET]: (state, action) => ({
      enabled: action.enabled,
      message: action.message || state.message
    })
  }),

  [menuSelectors.STORE_NAME]: menuReducer
}
