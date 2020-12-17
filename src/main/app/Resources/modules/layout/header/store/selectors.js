import {createSelector} from 'reselect'

const STORE_NAME = 'header'

const store = (state) => state[STORE_NAME]

const menus = createSelector(
  [store],
  (store) => store.menus
)

const display = createSelector(
  [store],
  (store) => store.display
)

export const selectors = {
  STORE_NAME,

  menus,
  display
}
