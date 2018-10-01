import {createSelector} from 'reselect'

const STORE_NAME = 'overlayStack'

const store = (state) => state[STORE_NAME]

const show = createSelector(
  [store],
  (store) => store && 0 !== store.length
)

export const selectors = {
  STORE_NAME,
  show
}
