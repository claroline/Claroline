import {createSelector} from 'reselect'

const STORE_NAME = 'administration'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const tools = createSelector(
  [store],
  (store) => store.tools
)

const defaultOpening = createSelector(
  [tools],
  (tools) => {
    let defaultTool = null
    if (tools[0]) {
      // open the first available tool
      defaultTool = tools[0].name
    }

    return defaultTool
  }
)

export const selectors = {
  STORE_NAME,

  loaded,
  tools,
  defaultOpening
}