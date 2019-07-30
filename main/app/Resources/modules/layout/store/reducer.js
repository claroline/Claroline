import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  SIDEBAR_OPEN,
  SIDEBAR_CLOSE
} from '#/main/app/layout/store/actions'

// menu
import {reducer as menuReducer} from '#/main/app/layout/menu/store/reducer'
import {selectors as menuSelectors} from '#/main/app/layout/menu/store/selectors'

export const reducer = {
  maintenance: makeReducer({enabled: false, message: null}),

  [menuSelectors.STORE_NAME]: menuReducer,

  sidebar: combineReducers({
    name: makeReducer(null, {
      [SIDEBAR_OPEN]: (state, action) => state !== action.toolName ? action.toolName : null,
      [SIDEBAR_CLOSE]: () => null
    })
  })
}
