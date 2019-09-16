import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

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
  if (!isEmpty(desktopTools)) {
    let defaultTool = configSelectors.param(state, 'desktop.defaultTool')

    if (!defaultTool || -1 === desktopTools.findIndex(tool => defaultTool === tool.name)) {
      // no default set or the default tool is not available for the user
      // open the first available tool
      defaultTool = desktopTools[0].name
    }

    return defaultTool
  }

  return null
}

export const selectors = {
  STORE_NAME,

  loaded,
  tools,
  defaultOpening
}