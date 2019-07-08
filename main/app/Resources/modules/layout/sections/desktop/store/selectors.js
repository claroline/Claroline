import {createSelector} from 'reselect'

const STORE_NAME = 'desktop'

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

const history = createSelector(
  [store],
  (store) => store.history
)

const historyLoaded = createSelector(
  [history],
  (history) => history.loaded
)

const historyResults = createSelector(
  [history],
  (history) => history.results
)

export const selectors = {
  STORE_NAME,

  loaded,
  tools,
  defaultOpening,
  historyLoaded,
  historyResults
}