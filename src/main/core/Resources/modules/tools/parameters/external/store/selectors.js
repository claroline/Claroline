import {createSelector} from 'reselect'

const STORE_NAME = 'userExternalParameters'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const accounts = createSelector(
  [store],
  (store) => store.accounts
)

export const selectors = {
  STORE_NAME,

  loaded,
  accounts
}
