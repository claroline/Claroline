import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  MENU_OPEN,
  MENU_CLOSE,
  MENU_TOGGLE,
  MENU_CHANGE_SECTION
} from '#/main/app/layout/menu/store/actions'

export const reducer = combineReducers({
  untouched: makeReducer(true, {
    [MENU_OPEN]: () => false,
    [MENU_CLOSE]: () => false,
    [MENU_TOGGLE]: () => false
  }),
  opened: makeReducer(true, {
    [MENU_OPEN]: () => true,
    [MENU_CLOSE]: () => false,
    [MENU_TOGGLE]: (state) => !state
  }),
  section: makeReducer(null, {
    [MENU_CHANGE_SECTION]: (state, action) => action.section
  })
})
