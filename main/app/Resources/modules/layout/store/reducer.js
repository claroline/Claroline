import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  SIDEBAR_OPEN,
  SIDEBAR_CLOSE
} from '#/main/app/layout/store/actions'

// config
import {reducer as configReducer} from '#/main/app/config/store/reducer'
import {selectors as configSelectors} from '#/main/app/config/store/selectors'

// security
import {reducer as securityReducer} from '#/main/app/security/store/reducer'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

// menu
import {reducer as menuReducer} from '#/main/app/layout/menu/store/reducer'
import {selectors as menuSelectors} from '#/main/app/layout/menu/store/selectors'

export const reducer = {
  maintenance: makeReducer({enabled: false, message: null}),

  [configSelectors.STORE_NAME]: configReducer,
  [securitySelectors.STORE_NAME]: securityReducer,

  [menuSelectors.STORE_NAME]: menuReducer,

  sidebar: combineReducers({
    name: makeReducer(null, {
      [SIDEBAR_OPEN]: (state, action) => state !== action.toolName ? action.toolName : null,
      [SIDEBAR_CLOSE]: () => null
    })
  })
}
