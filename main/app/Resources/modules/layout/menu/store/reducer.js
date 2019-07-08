import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  MENU_TOGGLE,
  MENU_CHANGE_SECTION
} from '#/main/app/layout/menu/store/actions'

export const reducer = combineReducers({
  opened: makeReducer(true, {
    [MENU_TOGGLE]: (state) => !state
  }),
  section: makeReducer(null, {
    [MENU_CHANGE_SECTION]: (state, action) => action.section
  })
})
