import {createSelector} from 'reselect'

const STORE_NAME = 'user_management'

const store = (state) => state[STORE_NAME]

const platformRoles = createSelector(
  [store],
  (store) => store.platformRoles
)

export const selectors = {
  STORE_NAME,

  platformRoles
}
