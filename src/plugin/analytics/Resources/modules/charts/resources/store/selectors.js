import {createSelector} from 'reselect'

const STORE_NAME = 'resourcesChart'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const mode = createSelector(
  [store],
  (store) => store.mode
)

const data = createSelector(
  [store],
  (store) => store.data
)

export const selectors = {
  STORE_NAME,

  loaded,
  mode,
  data
}
