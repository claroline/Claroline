import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store'

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

const defaultOpening = (state) => {
  const desktopTools = tools(state)
  let defaultTool = configSelectors.param(state, 'desktop.defaultTool')

  if (!defaultTool && desktopTools[0]) {
    // open the first available tool
    defaultTool = desktopTools[0].name
  }

  return defaultTool
}

export const selectors = {
  STORE_NAME,

  loaded,
  tools,
  defaultOpening
}