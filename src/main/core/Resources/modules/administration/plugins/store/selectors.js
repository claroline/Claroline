import {createSelector} from 'reselect'

const STORE_NAME = 'plugins'

const store = (state) => state[STORE_NAME]

const plugin = createSelector(
  [store],
  (store) => store.plugin
)

export const selectors = {
  STORE_NAME,
  store,
  plugin
}
