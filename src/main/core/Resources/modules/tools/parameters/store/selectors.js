import {createSelector} from 'reselect'

const STORE_NAME = 'parameters'

const store = (state) => state[STORE_NAME]

const tools = createSelector(
  [store],
  (store) => store.tools
)

const toolsConfig = createSelector(
  [store],
  (store) => store.toolsConfig
)

export const selectors = {
  STORE_NAME,
  tools,
  toolsConfig
}
