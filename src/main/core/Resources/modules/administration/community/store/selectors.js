import {createSelector} from 'reselect'

const STORE_NAME = 'community'

const store = (state) => state[STORE_NAME]

const platformRoles = createSelector(
  [store],
  (store) => store.platformRoles
)

const selected = createSelector(
  [store],
  (store) => store.users.compare.selected
)

export const selectors = {
  STORE_NAME,
  store,
  selected,
  platformRoles
}
