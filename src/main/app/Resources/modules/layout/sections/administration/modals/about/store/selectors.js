import {createSelector} from 'reselect'

const STORE_NAME = 'platformAbout'

const store = (state) => state[STORE_NAME]

const version = createSelector(
  [store],
  (store) => store.version
)

const changelogs = createSelector(
  [store],
  (store) => store.changelogs
)

export const selectors = {
  STORE_NAME,

  version,
  changelogs
}
