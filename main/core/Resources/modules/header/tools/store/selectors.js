import {createSelector} from 'reselect'

const STORE_NAME = 'toolsMenu'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const tools = createSelector(
  [store],
  (store) => store.tools
)

export const selectors = {
  STORE_NAME,

  store,
  loaded,
  tools
}
