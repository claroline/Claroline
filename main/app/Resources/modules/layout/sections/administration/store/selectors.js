import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store'

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

const defaultOpening = (state) => {
  const adminTools = tools(state)
  let defaultTool = configSelectors.param(state, 'admin.defaultTool')

  if (!defaultTool && adminTools[0]) {
    // open the first available tool
    defaultTool = adminTools[0].name
  }

  return defaultTool
}

export const selectors = {
  STORE_NAME,

  loaded,
  tools,
  defaultOpening
}