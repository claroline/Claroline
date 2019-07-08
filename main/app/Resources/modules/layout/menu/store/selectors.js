import {createSelector} from 'reselect'

const STORE_NAME = 'menu'

const store = (state) => state[STORE_NAME]

const opened = createSelector(
  [store],
  (store) => store.opened
)

const openedSection = createSelector(
  [store],
  (store) => store.section
)

export const selectors = {
  STORE_NAME,

  opened,
  openedSection
}
